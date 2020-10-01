<div class="mbv-tabs__pane mbv-is-visible" data-tab="template-editor">
	<textarea class="widefat mbv-editor" rows="20" id="mbv-post-content" name="post_content"><?= esc_textarea( get_post()->post_content ) ?></textarea>
</div>

<div class="mbv-tabs__pane" data-tab="css-editor">
	<textarea class="widefat mbv-editor" rows="20" id="mbv-post-excerpt" name="post_excerpt"><?= esc_textarea( get_post()->post_excerpt ) ?></textarea>
</div>

<div class="mbv-tabs__pane" data-tab="js-editor">
	<textarea class="widefat mbv-editor" rows="20" id="mbv-post-content-filtered" name="post_content_filtered"><?= esc_textarea( get_post()->post_content_filtered ) ?></textarea>
</div>