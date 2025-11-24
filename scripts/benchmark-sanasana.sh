#!/bin/bash
#
# Sanasana Performance Benchmark Script
# Mide TTFB y bootstrap time antes/después de optimizaciones
#
# Usage: ./benchmark-sanasana.sh [url]
# Example: ./benchmark-sanasana.sh http://localhost:8888/wordpress/

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
URL="${1:-http://localhost:8888/wordpress/}"
ITERATIONS=10
WARMUP=2

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "⚡ Sanasana Performance Benchmark"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🔗 URL: $URL"
echo "🔄 Iterations: $ITERATIONS (+ $WARMUP warmup)"
echo ""

# Function to measure TTFB
measure_ttfb() {
    local url=$1
    local name=$2
    
    echo -e "${YELLOW}📊 Measuring: $name${NC}"
    echo "────────────────────────────────────────────────"
    
    # Warmup requests
    echo "🔥 Warming up cache ($WARMUP requests)..."
    for i in $(seq 1 $WARMUP); do
        curl -o /dev/null -s -w "" "$url" 2>/dev/null || true
        sleep 0.5
    done
    
    # Actual measurements
    echo "📈 Running $ITERATIONS measurements..."
    local ttfb_sum=0
    local ttfb_min=999999
    local ttfb_max=0
    local ttfb_values=()
    
    for i in $(seq 1 $ITERATIONS); do
        # Measure TTFB in milliseconds
        ttfb=$(curl -o /dev/null -s -w "%{time_starttransfer}" "$url" 2>/dev/null | awk '{printf "%.0f", $1 * 1000}')
        
        ttfb_values+=($ttfb)
        ttfb_sum=$((ttfb_sum + ttfb))
        
        # Track min/max
        if [ $ttfb -lt $ttfb_min ]; then
            ttfb_min=$ttfb
        fi
        if [ $ttfb -gt $ttfb_max ]; then
            ttfb_max=$ttfb
        fi
        
        printf "  #%2d: %4dms\n" "$i" "$ttfb"
        sleep 0.3
    done
    
    # Calculate average
    local ttfb_avg=$((ttfb_sum / ITERATIONS))
    
    # Calculate median (simple sort and middle value)
    IFS=$'\n' sorted=($(sort -n <<<"${ttfb_values[*]}"))
    local median_idx=$((ITERATIONS / 2))
    local ttfb_median=${sorted[$median_idx]}
    
    echo ""
    echo -e "${GREEN}✅ Results for: $name${NC}"
    printf "   Average: %4dms\n" "$ttfb_avg"
    printf "   Median:  %4dms\n" "$ttfb_median"
    printf "   Min:     %4dms\n" "$ttfb_min"
    printf "   Max:     %4dms\n" "$ttfb_max"
    echo ""
    
    # Return avg (for comparison)
    echo "$ttfb_avg"
}

# Test scenarios
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🧪 Test 1: Homepage (likely with shortcodes)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
home_ttfb=$(measure_ttfb "$URL" "Homepage")

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🧪 Test 2: Blog page (likely without shortcodes)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
blog_ttfb=$(measure_ttfb "${URL}blog/" "Blog Page")

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🧪 Test 3: Single post (mixed content)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
# Try to get latest post URL
post_url=$(curl -s "$URL" | grep -o 'href="[^"]*\/[0-9][0-9][0-9][0-9]\/[^"]*"' | head -1 | sed 's/href="//;s/"$//' || echo "${URL}sample-page/")
post_ttfb=$(measure_ttfb "$post_url" "Single Post")

# Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 SUMMARY"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
printf "Homepage:    %4dms\n" "$home_ttfb"
printf "Blog:        %4dms\n" "$blog_ttfb"
printf "Single Post: %4dms\n" "$post_ttfb"
echo ""
echo "💡 Expected with optimizations (v1.0.5):"
echo "   - Homepage: 500-700ms (cached queries + lazy load)"
echo "   - Blog:     400-600ms (no shortcodes = lazy load savings)"
echo "   - Post:     450-650ms (mixed content)"
echo ""
echo "🎯 Target improvements vs baseline:"
echo "   - Cache system: -300-500ms TTFB"
echo "   - Lazy loading: -30-100ms bootstrap"
echo "   - Total: -330-600ms"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check for Query Monitor or debug info
echo ""
echo "🔍 Additional metrics (if available):"
echo "   - Check wp-admin for Query Monitor plugin"
echo "   - Admin bar shows: Cache Stats + Lazy Load Stats"
echo "   - View page source for <!-- Cache Stats --> comment"
echo ""

# Instructions
echo "📝 To compare before/after:"
echo "   1. Run this script NOW (with optimizations)"
echo "   2. Disable optimizations in wp-config.php:"
echo "      define('SANASANA_DISABLE_CACHE', true);"
echo "      define('SANASANA_DISABLE_LAZY_LOAD', true);"
echo "   3. Flush all cache (admin bar button)"
echo "   4. Run this script again"
echo "   5. Compare results"
echo ""
echo "✅ Benchmark complete!"
