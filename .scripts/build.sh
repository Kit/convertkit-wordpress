# Build ACTIONS-FILTERS.md
php create-actions-filters-docs.php

# Generate .pot file
php -n $(which wp) i18n make-pot ../ ../languages/convertkit.pot

# Build ZIP file, excluding non-Plugin files
cd ..
rm convertkit.zip
zip -r convertkit.zip . \
-x "*.devcontainer*" \
-x "*.git*" \
-x ".scripts/*" \
-x ".wordpress-org/*" \
-x "log/*" \
-x "tests/*" \
-x "vendor/*" \
-x "!vendor/convertkit" \
-x "*.DS_Store" \
-x "*.distignore" \
-x "*.env" \
-x "*.md" \
-x "*.dist" \
-x "*.example" \
-x "*.neon" \
-x "*.testing" \
-x "*.xml" \
-x "*.yml" \
-x "*.zip" \
-x "composer.json" \
-x "composer.lock"
