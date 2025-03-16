const esbuild = require("esbuild");
const shouldWatch = process.argv.includes("--watch");

async function buildWithWatch(options) {
  const ctx = await esbuild.context(options);
  if (shouldWatch) {
    await ctx.watch();
  } else {
    await ctx.rebuild();
    await ctx.dispose();
  }
}

const commonOptions = {
  sourcemap: "external",
  define: {
    "process.env.NODE_ENV": shouldWatch ? `'production'` : `'development'`,
  },
  bundle: true,
  mainFields: ["module", "main"],
  minifySyntax: true,
  minifyWhitespace: true,
};

buildWithWatch({
  ...commonOptions,
  entryPoints: [`resources/js/index.js`],
  outfile: `dist/index.js`,
  platform: "browser",
}).catch(() => process.exit(1));

const formComponents = [
  "filament-google-geocomplete",
  "filament-google-maps",
  "filament-google-maps-widget",
  "filament-google-maps-entry",
];

formComponents.forEach((component) => {
  buildWithWatch({
    ...commonOptions,
    entryPoints: [`resources/js/${component}.js`],
    outfile: `dist/cheesegrits/filament-google-maps/${component}.js`,
    platform: "neutral",
  }).catch(() => process.exit(1));
});
