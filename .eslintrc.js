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
	rules: {
		// Globals are not camelcase; in the future, we should update JS to meet camelcase standards.
		camelcase: 'off',
		// We don't yet manage dependencies, so some files report functions that are not defined, as they're in different files,
		// despite being enqueued on the same page.
		// In the future, we will use `wp-scripts build` to build single backend + frontend JS, which will fix this issue.
		'no-undef': 'off',
		// If debugging is enabled in the Plugin, we deliberately output to the console.
		'no-console': 'off',
		// We use a blocking confirm() dialog in the Plugin's Setup Wizard.
		'no-alert': 'off',
	},
};
