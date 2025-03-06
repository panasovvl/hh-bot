import gulp from "gulp";
// import less from "gulp-less";
import del from "del";
import cleanCSS from "gulp-clean-css";
import rename from "gulp-rename";
import babel from "gulp-babel";
import uglify from "gulp-uglify";
import concat from "gulp-concat";
import sourcemaps from "gulp-sourcemaps";
import autoprefixer from "gulp-autoprefixer";
import imagemin from "gulp-imagemin";
import htmlmin from "gulp-htmlmin";
import newer from "gulp-newer";
import browsersync from "browser-sync";
import destclean from "gulp-dest-clean";
import pug from "gulp-pug";
import gulpIf from "gulp-if";
import gulpInsert from "gulp-insert";

global.app = {
  isBuild: process.argv.includes("--build"),
  isDev: !process.argv.includes("--build"),
};

const paths = {
  styles: {
    src: "src/styles/**/*.css",
    dest: "dist/css/",
  },
  scripts: {
    src: "src/scripts/**/*.js",
    dest: "dist/js/",
  },
  imgs: {
    src: "src/img/**",
    dest: "dist/img/",
  },
  html: {
    src: "src/**/*.html",
    dest: "dist/",
  },
  php: {
    src: "src/php/**/*.php",
    dest: "dist/",
  },
  pug: {
    src: "src/**/*.pug",
    dest: "dist/",
  },
  gulp: {
    src: "src/gulp/",
  }
};

gulp.task("clean", () => {
  return del(["dist/*", "!dist/img/"]);
});

gulp.task("styles", () => {
  return (
    gulp
      .src(paths.styles.src)
      .pipe(sourcemaps.init())
      // .pipe(less())
      .pipe(
        autoprefixer({
          cascade: false,
        })
      )
      .pipe(
        cleanCSS({
          level: 2,
        })
      )
      .pipe(
        rename({
          basename: "main",
          suffix: ".min",
        })
      )
      .pipe(sourcemaps.write("."))
      .pipe(gulp.dest(paths.styles.dest))
      .pipe(browsersync.stream())
  );
});

gulp.task("scripts", () => {
  return gulp
    .src(paths.scripts.src)
    .pipe(sourcemaps.init())
    .pipe(
      babel({
        // presets: ["@babel/env"],
      })
    )
    .pipe(uglify())
    .pipe(concat("main.min.js"))
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest(paths.scripts.dest))
    .pipe(browsersync.stream());
});

gulp.task("imagemin", () => {
  return gulp
    .src(paths.imgs.src, { encoding: false })
    .pipe(destclean(paths.imgs.dest))
    .pipe(newer(paths.imgs.dest))
    .pipe(
      imagemin({
        //        progressive: true
      })
    )
    .pipe(gulp.dest(paths.imgs.dest))
    .pipe(browsersync.stream());
});

gulp.task("htmlmin", () => {
  return gulp
    .src(paths.html.src)
    .pipe(htmlmin({ collapseWhitespace: true }))
    .pipe(gulp.dest(paths.html.dest))
    .pipe(browsersync.stream());
});

gulp.task("pug", () => {
  return gulp
    .src(paths.pug.src)
    .pipe(
      pug({
        pretty: true,
      })
    )
    .pipe(gulpIf(app.isBuild, htmlmin({ collapseWhitespace: true })))
    .pipe(gulp.dest(paths.html.dest))
    .pipe(browsersync.stream());
});

gulp.task("php", () => {
  return gulp
    .src(paths.php.src)
    .pipe(gulpIf(app.isDev, gulpInsert.transform(function (contents, file) {
      return contents.replace(/<\/body>\s*<\/html>\s*$/mi, "<script id=\"__bs_script__\">//<![CDATA[\n" +
        "document.write(\"<script async src='https://localhost:3000/browser-sync/browser-sync-client.js?v=3.0.3'><\\/script>\");\n" +
        "//]]></script>\n" +
        "</body>\n</html>");
    })))
    .pipe(gulpIf(app.isBuild, htmlmin({
      collapseWhitespace: true,
      // ignoreCustomFragments: [/<%[\s\S]*?%>/, /<\?[=|php]?[\s\S]*?\?>/]
    })))
    .pipe(gulp.dest(paths.php.dest))
    .pipe(browsersync.stream());
});

gulp.task("watch", () => {
  browsersync.init({
    socket: {
      domain: "localhost:3000",
    },
    https: {
      key: paths.gulp.src + "ssl/localhost+4-key.pem",
      cert: paths.gulp.src + "ssl/localhost+4.pem",
    },
  });
  // browsersync.init({
  //   server: paths.html.dest,
  // });
  gulp.watch(paths.styles.src, gulp.series("styles"));
  gulp.watch(paths.scripts.src, gulp.series("scripts"));
  gulp.watch(paths.imgs.src, gulp.series("imagemin"));
  gulp.watch(paths.html.src, gulp.series("htmlmin"));
  gulp.watch(paths.pug.src, gulp.series("pug"));
  gulp.watch(paths.php.src, gulp.series("php"));
});

gulp.task(
  "build",
  gulp.series(
    "clean",
    gulp.parallel("htmlmin", "pug", "php", "styles", "scripts", "imagemin"),
    "watch"
  )
);

gulp.task("default", gulp.series("build"));
