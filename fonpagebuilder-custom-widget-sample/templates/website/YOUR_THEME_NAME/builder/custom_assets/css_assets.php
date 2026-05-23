<?php defined('CORE_FOLDER') OR exit('You can not get in here!'); ?>

<?php if (isset($hoptions) && in_array("custom-css-asset-library",$hoptions,true)):?>
    <link rel="stylesheet" href="<?php echo $tadress; ?>css/custom-css-asset-library.css">
<?php endif; ?>