<?php
$id   = wp_unique_id('bPluginsCustomHtml-');
$HTML = $attributes['HTML'];
$isRenderViaIframe = $attributes['options']['isRenderViaIframe'] ?? false;
$isDisplayCodeToFrontend = $attributes['options']['displayCodeToFrontend'];

if (empty($isDisplayCodeToFrontend) && empty($isRenderViaIframe)) {
    ?>
    <div <?php echo get_block_wrapper_attributes(); ?> id='<?php echo esc_attr($id); ?>'>
    <?php
    echo $HTML;
    ?>
    </div>
    <?php
} else {
?>
<div <?php echo get_block_wrapper_attributes(); ?> id='<?php echo esc_attr($id); ?>' data-attributes='<?php echo esc_attr(wp_json_encode($attributes)); ?>' ></div>
<?php
}



