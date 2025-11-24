/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.{php,html,js,jsx,ts,tsx}",          // todo el theme padre
    "./templates/**/*.{php,html}",              // plantillas
    "./inc/**/*.{php,html}",                    // helpers y tags
    "../p5marketing-child/**/*.{php,html,js}",  // si usas child
    "../../plugins/**/*.{php,html}",            // (opcional) si tus plugins imprimen HTML
  ],
  safelist: [
    // utilidades que ya usas y pueden venir de contenido dinámico
    'container','prose','mx-auto','px-6','py-4','py-10','gap-6',
    'text-xl','font-semibold','border-b','border-t','min-h-screen','bg-white',
    'max-w-screen-2xl', 'px-4','sm:px-6','lg:px-8', 'flex','items-center','justify-between','gap-3','gap-8','text-lg','tracking-tight','backdrop-blur','sticky','top-0',
    // patrones comunes de Gutenberg
    { pattern: /^align/ },           // alignwide, alignfull, aligncenter
    { pattern: /^wp-block-/ },       // clases base de bloques

    // Gutenberg: utilidades responsive mínimas en campos "Additional CSS Class"
    // Mantener pequeño para no inflar el CSS pero permitir ajustes por breakpoint
    { pattern: /^(sm:|md:|lg:|xl:)?(hidden|block)$/ },
    { pattern: /^(sm:|md:|lg:|xl:)?(flex|grid)$/ },
    { pattern: /^(sm:|md:|lg:|xl:)?items-(start|center|end)$/ },
    { pattern: /^(sm:|md:|lg:|xl:)?justify-(start|center|end|between)$/ },
    { pattern: /^(sm:|md:|lg:|xl:)?gap-(0|1|2|3|4|6|8|10)$/ },
    { pattern: /^(sm:|md:|lg:|xl:)?p([trblxy])?-(0|1|2|3|4|6|8|10)$/ },
    { pattern: /^(sm:|md:|lg:|xl:)?m([trblxy])?-(0|1|2|3|4|6|8|10)$/ },
    { pattern: /^(sm:|md:|lg:|xl:)?text-(xs|sm|base|lg|xl|2xl)$/ },

    // (Patrones Beaver Builder removidos a petición)
  ],
  theme: { extend: {} },
  plugins: [require("@tailwindcss/typography"), require("@tailwindcss/forms")],
};
