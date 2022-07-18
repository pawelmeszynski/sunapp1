const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');
let glob = require('glob');

let dirs = [
    {
        source: 'resources/assets/img',
        dest: 'resources/dist/images',
    },
];

mix.setPublicPath('resources/dist');
mix.mergeManifest();
mix.sourceMaps();
mix.setResourceRoot('../');

//mix.copy('resources/assets/img', 'resources/dist/images');
mix.sass('resources/assets/css/style.scss', 'css/style.css');

dirs.forEach((dir) => {
    mix.copy(dir.source, dir.dest, false);
});

let files = [];
dirs.forEach((dir) => {
    glob.sync('**/*', {cwd: dir.source}).forEach((file) => {
        files.push(dir.dest + '/' + file);
    });
});

if (mix.inProduction()) {
    mix.version(files);
}
