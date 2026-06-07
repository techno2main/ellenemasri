<?php
$builder_css_version = file_exists(__DIR__ . '/assets/builder.css') ? (string) filemtime(__DIR__ . '/assets/builder.css') : '1';
$builder_js_version = file_exists(__DIR__ . '/assets/builder.js') ? (string) filemtime(__DIR__ . '/assets/builder.js') : '1';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>VISUAL LINKS BUILDER (VLB)</title>
	<link rel="stylesheet" href="assets/builder.css?v=<?php echo esc_attr($builder_css_version); ?>">
</head>
<body>
	<div class="container">
		<div class="main-content">
			<div class="canvas-area">
				<div class="upload-zone" id="uploadZone">
					<p style="margin-bottom: 10px; font-size: 14px;">📁 Cliquez ou glissez votre image ici</p>
					<p style="font-size: 12px; color: #666;">Formats acceptés : JPG, PNG, GIF</p>
					<button type="button" id="uploadFromMediaBtn" class="upload-zone__media-btn">Choisir depuis la bibliothèque média</button>
					<input type="file" id="imageUpload" accept="image/*">
				</div>

				<div class="canvas-wrapper" id="canvasWrapper" style="display: none;">
					<img id="imageCanvas" src="" alt="Image cliquable">
					<div id="overlayContainer"></div>
				</div>

				<div class="info-box">
					<strong>Mode d'emploi :</strong>
					1. Chargez votre image depuis l'ordinateur ou la bibliothèque média<br>
					2. Cliquez et glissez pour dessiner des zones rectangulaires<br>
					3. Ajoutez un lien ou une ancre pour chaque zone<br>
					4. Ouvrez Preview puis cliquez sur Template E-Mail
				</div>
			</div>

			<?php include __DIR__ . '/partials/sidebar.php'; ?>
		</div>
	</div>

	<?php include __DIR__ . '/partials/modals.php'; ?>

	<script src="assets/builder.js?v=<?php echo esc_attr($builder_js_version); ?>" defer></script>
</body>
</html>
