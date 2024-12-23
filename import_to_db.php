<?php
/** @var PDO $pdo */
$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// подготовка SQL-запросов для вставки данных в таблицы
$brands = $pdo->prepare("INSERT INTO brands (id, name, url, bold, done) VALUES (?, ?, ?, ?, ?)");
$models = $pdo->prepare("INSERT INTO models (brands_id, name, url, hasPanorama, done) VALUES (?, ?, ?, ?, ?)");
$generations = $pdo->prepare("INSERT INTO generations  (model_id, src, src2x, url, title, generationInfo, isNewAuto, isComingSoon, frames, frameTypes, groupSalug, groupShort) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$complectations = $pdo->prepare("INSERT INTO complectations (generation_id, name, url, group_name) VALUES (?, ?, ?, ?)");

// чтение содержимого файла json
$content = file_get_contents('Audi.json');
// JSON в массив
$array = json_decode($content, true);

$bold = $array['bold'] ? 1: 0;

// запрос для вставки данных о бренде в таблицу brands
$brands->execute([$array['id'], $array['name'], $array['url'], $bold, $array['done']]);
// получаем ID последней вставленной записи в таблицу brands
$brands_id = $pdo->lastInsertId();

// проверка успешности вставки, если ID не получен, заканчиваем выполнение
if (!$brands_id) {
    die("Ошибка вставки в таблицу");
}

// прохождение по всем моделям в массиве и добавление их в models
foreach ($array['models'] as $item) {
    $hasPanorama = $item['hasPanorama'] ? 1 : 0;
    $done = $item['done'] ? 1 : 0;

    // запрос для вставки модели в таблицу models
    $models->execute([$brands_id, $item['name'], $item['url'], $hasPanorama, $done]);
    $model_id = $pdo->lastInsertId();

    // прохождение по всем поколениям модели и добавляем их в таблицу generations
    foreach ($item['generations'] as $generation) {
        $isNewAuto = $generation['isNewAuto'] ? 1 : 0;
        $isComingSoon = $generation['isComingSoon'] ? 1 : 0;

        // запрос для вставки поколения в таблицу generations
        $generations->execute([$model_id, $generation['src'], $generation['src2x'], $generation['url'], $generation['title'], $generation['generationInfo'], $isNewAuto, $isComingSoon, $generation['frames'], $generation['frameTypes'], $generation['groupSalug'], $generation['groupShort']]);
        $generation_id = $pdo->lastInsertId();

        // прохождение по всем комплектациям поколения и добавляем их в таблицу complectations
        foreach ($generation['complectations'] as $complectation) {
            $complectations->execute([$generation_id, $complectation['name'], $complectation['url'], $complectation['group_name']]);
        }
    }
}
