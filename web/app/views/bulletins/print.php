<!DOCTYPE html>
<html lang="<?= e(Lang::locale()) ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(t('Report Card')) ?></title>
<style>body{margin:0;padding:16px;background:#fff}@media print{body{padding:0}.no-print{display:none}}.no-print{max-width:900px;margin:0 auto 12px;text-align:right}.no-print button{padding:8px 16px;border:0;border-radius:8px;background:#5d5fef;color:#fff;cursor:pointer}</style>
<style><?= $rendered['css'] ?></style>
</head>
<body>
<div class="no-print"><button onclick="window.print()"><?= e(t('Print / Save PDF')) ?></button></div>
<?= $rendered['html'] ?>
</body>
</html>
