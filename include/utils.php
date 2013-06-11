<?php
function renderTipImg( $string ) {
    ?>
    <img
      class="help"
      src="img/help18.png"
      title="<?php echo get_string($string, "block_smartblock"); ?>"/>
   <?php
}
?>