<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$map_type = get_sub_field('map_type');

?>

<!-- Maps Section Start -->
<section class="maps_section">

    <div class="container-fluid">
        <?php if ($map_type == 'code'):?>

            <?php the_sub_field('map_code'); ?>

        <?php elseif ($map_type == 'api'):?>

            <div class="map" id="map"></div>

                <script>
                    // Инициализируем карту
                    var map = L.map('map').setView([65.7796236, 24.5519743], 18);

                    // Добавляем слои карт OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    // Массив для маркеров
                    var markers = [];

                    <?php if (have_rows('map_coordinates')): ?>
                    <?php while (have_rows('map_coordinates')) : the_row(); ?>
                        <?php
                        $lat = get_sub_field('lat');
                        $lng = get_sub_field('lng');
                        $name = get_sub_field('name');
                        ?>

                        // Инициализация новой иконки
                        var myIcon = L.icon({
                        iconUrl: '/wp-content/themes/palsanmaki/assets/images/icons/pin2.png',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -32]
                        });

                        // Создаем маркер с новой иконкой
                        var marker = L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>], { icon: myIcon }).addTo(map);

                        // Добавляем попап с названием
                        marker.bindPopup("<?php echo $name; ?>");

                        // Сохраняем маркер в массив
                        markers.push(marker);
                    <?php endwhile; ?>
                    <?php endif; ?>

                    // Группируем маркеры в слой
                    var group = new L.featureGroup(markers);
                    // Добавляем все маркеры на карт
                    map.fitBounds(group.getBounds());
                </script>

        <?php endif;?>
    </div>

</section>
<!-- Maps Section END -->