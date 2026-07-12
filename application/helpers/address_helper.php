<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('get_oriental_mindoro_barangays')) {
    /**
     * Municipality/city => barangays for Oriental Mindoro (source: PSA/PhilAtlas).
     * Mirrors the dropdown data in views/auth/register.php so the server can
     * reject municipality/barangay combinations that didn't come from that
     * dropdown (e.g. a tampered POST request).
     */
    function get_oriental_mindoro_barangays() {
        return array(
            'Calapan City' => array('Balingayan','Balite','Baruyan','Batino','Bayanan I','Bayanan II','Biga','Bondoc','Bucayao','Buhuan','Bulusan','Calero','Camansihan','Camilmil','Canubing I','Canubing II','Comunal','Guinobatan','Gulod','Gutad','Ibaba East','Ibaba West','Ilaya','Lalud','Lazareto','Libis','Lumangbayan','Mahal na Pangalan','Maidlang','Malad','Malamig','Managpi','Masipit','Nag-iba I','Nag-iba II','Navotas','Pachoca','Palhi','Panggalaan','Parang','Patas','Personas','Putingtubig','Salong','San Antonio','San Vicente Central','San Vicente East','San Vicente North','San Vicente South','San Vicente West','Santa Cruz','Santa Isabel','Santa Maria Village','Santa Rita','Santo Niño','Sapul','Silonay','Suqui','Tawagan','Tawiran','Tibag','Wawa'),
            'Baco' => array('Alag','Bangkatan','Baras','Bayanan','Burbuli','Catwiran I','Catwiran II','Dulangan I','Dulangan II','Lantuyang','Lumangbayan','Malapad','Mangangan I','Mangangan II','Mayabig','Pambisan','Poblacion','Pulang-Tubig','Putican-Cabulo','San Andres','San Ignacio','Santa Cruz','Santa Rosa I','Santa Rosa II','Tabon-tabon','Tagumpay','Water'),
            'Bansud' => array('Alcadesma','Bato','Conrazon','Malo','Manihala','Pag-asa','Poblacion','Proper Bansud','Proper Tiguisan','Rosacara','Salcedo','Sumagui','Villa Pag-asa'),
            'Bongabong' => array('Anilao','Aplaya','Bagumbayan I','Bagumbayan II','Batangan','Bukal','Camantigue','Carmundo','Cawayan','Dayhagan','Formon','Hagan','Hagupit','Ipil','Kaligtasan','Labasan','Labonan','Libertad','Lisap','Luna','Malitbog','Mapang','Masaguisi','Mina de Oro','Morente','Ogbot','Orconuma','Poblacion','Polusahi','Sagana','San Isidro','San Jose','San Juan','Santa Cruz','Sigange','Tawas'),
            'Bulalacao' => array('Bagong Sikat','Balatasan','Benli','Cabugao','Cambunang','Campaasan','Maasin','Maujao','Milagrosa','Nasukob','Poblacion','San Francisco','San Isidro','San Juan','San Roque'),
            'Gloria' => array('Agos','Agsalin','Alma Villa','Andres Bonifacio','Balete','Banus','Banutan','Bulaklakan','Buong Lupa','Gaudencio Antonino','Guimbonan','Kawit','Lucio Laurel','Macario Adriatico','Malamig','Malayong','Maligaya','Malubay','Manguyang','Maragooc','Mirayan','Narra','Papandungin','San Antonio','Santa Maria','Santa Theresa','Tambong'),
            'Mansalay' => array('B. del Mundo','Balugo','Bonbon','Budburan','Cabalwa','Don Pedro','Maliwanag','Manaul','Panaytayan','Poblacion','Roma','Santa Brigida','Santa Maria','Santa Teresita','Villa Celestial','Wasig','Waygan'),
            'Naujan' => array('Adrialuna','Andres Ilagan','Antipolo','Apitong','Arangin','Aurora','Bacungan','Bagong Buhay','Balite','Bancuro','Banuton','Barcenaga','Bayani','Buhangin','Caburo','Concepcion','Dao','Del Pilar','Estrella','Evangelista','Gamao','General Esco','Herrera','Inarawan','Kalinisan','Laguna','Mabini','Magtibay','Mahabang Parang','Malaya','Malinao','Malvar','Masagana','Masaguing','Melgar A','Melgar B','Metolza','Montelago','Montemayor','Motoderazo','Mulawin','Nag-iba I','Nag-iba II','Pagkakaisa','Paitan','Paniquian','Pinagsabangan I','Pinagsabangan II','Piñahan','Poblacion I','Poblacion II','Poblacion III','Sampaguita','San Agustin I','San Agustin II','San Andres','San Antonio','San Carlos','San Isidro','San Jose','San Luis','San Nicolas','San Pedro','Santa Cruz','Santa Isabel','Santa Maria','Santiago','Santo Niño','Tagumpay','Tigkan'),
            'Pinamalayan' => array('Anoling','Bacungan','Bangbang','Banilad','Buli','Cacawan','Calingag','Del Razon','Guinhawa','Inclanay','Lumangbayan','Malaya','Maliangcog','Maningcol','Marayos','Marfrancisco','Nabuslot','Pagalagala','Palayan','Pambisan Malaki','Pambisan Munti','Panggulayan','Papandayan','Pili','Quinabigan','Ranzo','Rosario','Sabang','Santa Isabel','Santa Maria','Santa Rita','Santo Niño','Wawa','Zone I','Zone II','Zone III','Zone IV'),
            'Pola' => array('Bacawan','Bacungan','Batuhan','Bayanan','Biga','Buhay na Tubig','Calima','Calubasanhon','Campamento','Casiligan','Malibago','Maluanluan','Matulatula','Misong','Pahilahan','Panikihan','Pula','Puting Cacao','Tagbakin','Tagumpay','Tiguihan','Zone I','Zone II'),
            'Puerto Galera' => array('Aninuan','Baclayan','Balatero','Dulangan','Palangan','Poblacion','Sabang','San Antonio','San Isidro','Santo Niño','Sinandigan','Tabinay','Villaflor'),
            'Roxas' => array('Bagumbayan','Cantil','Dangay','Happy Valley','Libertad','Libtong','Little Tanauan','Mabuhay','Maraska','Odiong','Paclasan','San Aquilino','San Isidro','San Jose','San Mariano','San Miguel','San Rafael','San Vicente','Uyao','Victoria'),
            'San Teodoro' => array('Bigaan','Caagutayan','Calangatan','Calsapa','Ilag','Lumangbayan','Poblacion','Tacligan'),
            'Socorro' => array('Bagsok','Batong Dalig','Bayuin','Bugtong na Tuog','Calocmoy','Calubayan','Catiningan','Fortuna','Happy Valley','Leuteboro I','Leuteboro II','Ma. Concepcion','Mabuhay I','Mabuhay II','Malugay','Matungao','Monteverde','Pasi I','Pasi II','Santo Domingo','Subaan','Villareal','Zone I','Zone II','Zone III','Zone IV'),
            'Victoria' => array('Alcate','Antonino','Babangonan','Bagong Buhay','Bagong Silang','Bambanin','Bethel','Canaan','Concepcion','Duongan','Jose Leido Jr.','Loyal','Mabini','Macatoc','Malabo','Merit','Ordovilla','Pakyas','Poblacion I','Poblacion II','Poblacion III','Poblacion IV','Sampaguita','San Antonio','San Cristobal','San Gabriel','San Gelacio','San Isidro','San Juan','San Narciso','Urdaneta','Villa Cerveza'),
        );
    }
}

if (!function_exists('is_valid_oriental_mindoro_address')) {
    /**
     * Returns TRUE only if $municipality is a known Oriental Mindoro LGU and
     * $barangay is one of its actual barangays.
     */
    function is_valid_oriental_mindoro_address($municipality, $barangay) {
        $data = get_oriental_mindoro_barangays();
        return isset($data[$municipality]) && in_array($barangay, $data[$municipality], TRUE);
    }
}
