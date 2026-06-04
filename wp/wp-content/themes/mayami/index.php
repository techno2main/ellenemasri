<?php
/**
 * Main template file
 * 
 * @package Mayami
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<main>
    <h1>Mayami Landing Page</h1>
    <p>Template en construction...</p>
</main>

<?php wp_footer(); ?>
</body>
</html>
