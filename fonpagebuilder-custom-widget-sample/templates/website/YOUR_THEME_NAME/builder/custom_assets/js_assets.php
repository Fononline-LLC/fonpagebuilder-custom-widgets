<?php defined('CORE_FOLDER') OR exit('You can not get in here!'); ?>

<?php if (isset($hoptions) && in_array("custom-js-asset-library",$hoptions,true)):?>
    <link rel="stylesheet" href="<?php echo $tadress; ?>js/custom-js-asset-library.js">
<?php endif; ?>