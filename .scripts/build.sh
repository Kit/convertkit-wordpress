# Build ACTIONS-FILTERS.md
php create-actions-filters-docs.php

# Generate .pot file
php -n $(which wp) i18n make-pot ../ ../languages/convertkit.pot

# Build ZIP file, excluding non-Plugin files
cd ..
rm convertkit.zip
zip -r convertkit.zip . \
-x "*.git*" \
-x ".devcontainer/*" \
-x ".scripts/*" \
-x ".wordpress-org/*" \
-x "log/*" \
-x "tests/*" \
-x "vendor/*" \
-x "*.distignore" \
-x "*.env.*" \
-x ".gitignore" \
-x "*.md" \
-x "*.yml" \
-x "composer.json" \
-x "composer.lock" \
-x "*.xml" \
-x "*.neon" \
-x "*.dist" \
-x "*.example" \
-x "*.DS_Store" \