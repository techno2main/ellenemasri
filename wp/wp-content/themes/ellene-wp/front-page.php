<?php

/**

 * Front Page Template - ellene-wp Landing Page

 * 

 * @package ElleneWp

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

<body id="page-top" <?php body_class(); ?>>

<?php wp_body_open(); ?>



<main class="relative overflow-x-clip">

    <!-- Modules (pilotage complet depuis l'admin) -->

    <?php get_template_part('template-parts/layout/content'); ?>

    

</main>



<?php wp_footer(); ?>

</body>

</html>

