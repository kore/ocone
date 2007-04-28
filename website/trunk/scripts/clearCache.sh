#/bin/bash
find htdocs/ '(' -name '*.html' -o -name '*.rss' ')' -exec rm -vf {} \;
