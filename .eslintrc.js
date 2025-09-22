module.exports = {
	extends: 'plugin:@wordpress/eslint-plugin/recommended',
	globals: {
		// WordPress / PHP-exported globals.
		convertkit: 'readonly',
		convertkit_admin_tinymce: 'readonly',
		convertkit_blocks: 'readonly',
		convertkit_block_formatters: 'readonly',
		convertkit_broadcasts: 'readonly',
		convertkit_restrict_content: 'readonly',
		convertkit_pre_publish_actions: 'readonly',
		convertkit_quicktags: 'readonly',
		convertkit_shortcodes: 'readonly',
	},
	ignorePatterns: ['resources/backend/js/gutenberg-block-formatters.js'],
	rules: {
		// Turn off specific rules
		camelcase: 'off',
		'no-undef': 'off',
		'no-console': 'off',
		'no-alert': 'off',
	},
};
