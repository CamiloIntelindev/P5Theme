<?php
/**
 * Theme self-updates from GitHub releases.
 *
 * Checks the latest GitHub release for this repo and surfaces updates in WP Admin → Themes.
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Repository settings
const P5M_UPDATE_REPO      = 'CamiloIntelindev/P5Theme';
const P5M_UPDATE_THEME_SLUG = 'p5marketing';

/**
 * Optionally provide a token via constant or env to avoid GitHub rate limits.
 */
function p5m_get_github_token() {
  if (defined('P5M_GITHUB_TOKEN') && P5M_GITHUB_TOKEN) {
    return P5M_GITHUB_TOKEN;
  }
  $env = getenv('P5M_GITHUB_TOKEN');
  return $env ? $env : null;
}

/**
 * Fetch latest release data from GitHub with transient caching.
 */
function p5m_fetch_latest_release() {
  $cached = get_site_transient('p5m_theme_update_release');
  if ($cached !== false) return $cached;

  $api = 'https://api.github.com/repos/' . P5M_UPDATE_REPO . '/releases/latest';
  $headers = [
    'User-Agent' => 'p5marketing-theme-updater',
    'Accept' => 'application/vnd.github+json',
  ];
  $token = p5m_get_github_token();
  if ($token) {
    $headers['Authorization'] = 'Bearer ' . $token;
  }

  $resp = wp_remote_get($api, [
    'headers' => $headers,
    'timeout' => 10,
  ]);

  if (is_wp_error($resp)) {
    set_site_transient('p5m_theme_update_release', [], HOUR_IN_SECONDS * 2);
    return [];
  }

  $code = wp_remote_retrieve_response_code($resp);
  if ($code !== 200) {
    set_site_transient('p5m_theme_update_release', [], HOUR_IN_SECONDS * 2);
    return [];
  }

  $body = json_decode(wp_remote_retrieve_body($resp), true);
  if (!is_array($body)) {
    set_site_transient('p5m_theme_update_release', [], HOUR_IN_SECONDS * 2);
    return [];
  }

  $tag       = isset($body['tag_name']) ? ltrim($body['tag_name'], 'v') : null;
  $zip_url   = isset($body['zipball_url']) ? $body['zipball_url'] : null;
  $html_url  = isset($body['html_url']) ? $body['html_url'] : null;
  $changelog = isset($body['body']) ? $body['body'] : '';

  if (!$tag || !$zip_url) {
    set_site_transient('p5m_theme_update_release', [], HOUR_IN_SECONDS * 2);
    return [];
  }

  $data = [
    'version'    => $tag,
    'zip_url'    => $zip_url,
    'html_url'   => $html_url,
    'changelog'  => $changelog,
  ];

  set_site_transient('p5m_theme_update_release', $data, HOUR_IN_SECONDS * 6);
  return $data;
}

/**
 * Inject update info into the theme update transient.
 */
function p5m_theme_check_for_update($transient) {
  if (!is_object($transient)) {
    $transient = new stdClass();
  }

  $current = wp_get_theme(P5M_UPDATE_THEME_SLUG);
  if (!$current->exists()) return $transient;

  $release = p5m_fetch_latest_release();
  if (empty($release['version'])) return $transient;

  if (version_compare($release['version'], $current->get('Version'), '>')) {
    $transient->response[P5M_UPDATE_THEME_SLUG] = [
      'theme'       => P5M_UPDATE_THEME_SLUG,
      'new_version' => $release['version'],
      'package'     => $release['zip_url'],
      'url'         => $release['html_url'] ?: 'https://github.com/' . P5M_UPDATE_REPO,
    ];
  }

  return $transient;
}
add_filter('site_transient_update_themes', 'p5m_theme_check_for_update');

/**
 * Provide basic details in the theme info modal (from GitHub release body).
 */
function p5m_theme_updates_api($result, $action, $args) {
  if ($action !== 'theme_information' || empty($args->slug) || $args->slug !== P5M_UPDATE_THEME_SLUG) {
    return $result;
  }

  $current = wp_get_theme(P5M_UPDATE_THEME_SLUG);
  $release = p5m_fetch_latest_release();

  $result = new stdClass();
  $result->name = $current->get('Name');
  $result->slug = P5M_UPDATE_THEME_SLUG;
  $result->version = $release['version'] ?? $current->get('Version');
  $result->author = $current->get('Author');
  $result->homepage = 'https://github.com/' . P5M_UPDATE_REPO;
  $result->download_link = $release['zip_url'] ?? '';
  $result->sections = [
    'description' => $current->get('Description'),
    'changelog'   => !empty($release['changelog']) ? wpautop($release['changelog']) : __('Release notes are not available.', 'p5marketing'),
  ];

  return $result;
}
add_filter('themes_api', 'p5m_theme_updates_api', 10, 3);
