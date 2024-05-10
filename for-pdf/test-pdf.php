<?php
// Подключаем WordPress
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php'); // Для доступа к функциям WordPress
require_once get_template_directory() . '/dompdf/autoload.inc.php'; // Подключение Dompdf

// Используем классы Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Инициализируем объект Dompdf
$dompdf = new Dompdf();

// Задаем опции (например, включаем поддержку PHP в HTML)
$options = new Options();
$options->set('isPhpEnabled', true);
$dompdf->setOptions($options);

// Загружаем HTML для PDF
$html = '<h1>Hello, World!</h1>';
$dompdf->loadHtml($html);

// Устанавливаем размер бумаги и ориентацию
$dompdf->setPaper('A4', 'landscape');

// Рендерим PDF
$dompdf->render();

// Выводим PDF в браузер
$dompdf->stream("test-dompdf.pdf", ["Attachment" => false]);
