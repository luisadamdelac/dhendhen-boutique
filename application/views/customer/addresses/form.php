<?php
$is_edit = isset($address);
$current_municipality = $address['municipality'] ?? '';
$current_barangay = $address['barangay'] ?? '';
?>
<style>
    .addr-form-container { margin-top: 30px; max-width: 600px; }

    .addr-form-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .addr-form-card .form-group { margin-bottom: 20px; }

    .addr-form-card label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--dark-gray);
    }

    .addr-form-card input,
    .addr-form-card select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
    }

    .addr-form-card input:focus,
    .addr-form-card select:focus {
        outline: none;
        border-color: var(--primary-pink);
    }

    .addr-form-card input[readonly] {
        background: #f3f4f6;
        color: #666;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .btn-update {
        width: 100%;
        padding: 15px;
        background: var(--primary-pink);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-update:hover { background: var(--primary-pink-dark); }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-error { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--gray-600);
        margin-bottom: 15px;
        text-decoration: none;
        font-size: 14px;
    }

    .back-link:hover { color: var(--primary-pink); }

    @media (max-width: 600px) {
        .form-row { grid-template-columns: 1fr; }
    }

    .label-choice-group { display: flex; gap: 10px; }
    .label-choice-btn {
        flex: 1;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        background: white;
        font-size: 14px;
        font-family: inherit;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .label-choice-btn:hover { border-color: var(--primary-pink); }
    .label-choice-btn.selected {
        border-color: var(--primary-pink);
        background: var(--primary-pink);
        color: white;
    }
</style>

<a href="<?php echo BASE_URL; ?>addresses" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to My Addresses
</a>
<h1><i class="fas fa-map-marker-alt"></i> <?php echo $is_edit ? 'Edit Address' : 'Add New Address'; ?></h1>

<div class="addr-form-container">
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
        </div>
    <?php endif; ?>

    <div class="addr-form-card">
        <form method="POST" action="<?php echo BASE_URL; ?>addresses/<?php echo $is_edit ? 'edit/' . $address['address_id'] : 'add'; ?>">
            <?php if (!empty($return)): ?>
                <input type="hidden" name="return" value="<?php echo htmlspecialchars($return); ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Label As:</label>
                <div class="label-choice-group">
                    <button type="button" class="label-choice-btn" data-label="Work">Work</button>
                    <button type="button" class="label-choice-btn" data-label="Home">Home</button>
                </div>
                <input type="hidden" id="label" name="label" value="<?php echo htmlspecialchars($address['label'] ?? 'Home'); ?>">
            </div>

            <div class="form-group">
                <label>Province</label>
                <input type="text" value="Oriental Mindoro" readonly>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="municipality">Municipality / City</label>
                    <select id="municipality" name="municipality" required>
                        <option value="">Select Municipality / City</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select id="barangay" name="barangay" required disabled>
                        <option value="">Select Municipality first</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="street">Street (House No., Street)</label>
                <input type="text" id="street" name="street" maxlength="100" required value="<?php echo htmlspecialchars($address['street'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn-update">
                <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Address' : 'Save Address'; ?>
            </button>
        </form>
    </div>
</div>

<script>
    var ORIENTAL_MINDORO_BARANGAYS = {
        "Calapan City": ["Balingayan","Balite","Baruyan","Batino","Bayanan I","Bayanan II","Biga","Bondoc","Bucayao","Buhuan","Bulusan","Calero","Camansihan","Camilmil","Canubing I","Canubing II","Comunal","Guinobatan","Gulod","Gutad","Ibaba East","Ibaba West","Ilaya","Lalud","Lazareto","Libis","Lumang Bayan","Mahal na Pangalan","Maidlang","Malad","Malamig","Managpi","Masipit","Nag-iba I","Nag-iba II","Navotas","Pachoca","Palhi","Panggalaan","Parang","Patas","Personas","Putingtubig","Salong","San Antonio","San Vicente Central","San Vicente East","San Vicente North","San Vicente South","San Vicente West","Santa Cruz","Santa Isabel","Santa Maria Village","Santa Rita","Santo Niño","Sapul","Silonay","Suqui","Tawagan","Tawiran","Tibag","Wawa"],
        "Baco": ["Alag","Bangkatan","Baras","Bayanan","Burbuli","Catwiran I","Catwiran II","Dulangan I","Dulangan II","Lantuyang","Lumang Bayan","Malapad","Mangangan I","Mangangan II","Mayabig","Pambisan","Poblacion","Pulang-Tubig","Putican-Cabulo","San Andres","San Ignacio","Santa Cruz","Santa Rosa I","Santa Rosa II","Tabon-tabon","Tagumpay","Water"],
        "Bansud": ["Alcadesma","Bato","Conrazon","Malo","Manihala","Pag-asa","Poblacion","Proper Bansud","Proper Tiguisan","Rosacara","Salcedo","Sumagui","Villa Pag-asa"],
        "Bongabong": ["Anilao","Aplaya","Bagumbayan I","Bagumbayan II","Batangan","Bukal","Camantigue","Carmundo","Cawayan","Dayhagan","Formon","Hagan","Hagupit","Ipil","Kaligtasan","Labasan","Labonan","Libertad","Lisap","Luna","Malitbog","Mapang","Masaguisi","Mina de Oro","Morente","Ogbot","Orconuma","Poblacion","Polusahi","Sagana","San Isidro","San Jose","San Juan","Santa Cruz","Sigange","Tawas"],
        "Bulalacao": ["Bagong Sikat","Balatasan","Benli","Cabugao","Cambunang","Campaasan","Maasin","Maujao","Milagrosa","Nasukob","Poblacion","San Francisco","San Isidro","San Juan","San Roque"],
        "Gloria": ["Agos","Agsalin","Alma Villa","Andres Bonifacio","Balete","Banus","Banutan","Bulaklakan","Buong Lupa","Gaudencio Antonino","Guimbonan","Kawit","Lucio Laurel","Macario Adriatico","Malamig","Malayong","Maligaya","Malubay","Manguyang","Maragooc","Mirayan","Narra","Papandungin","San Antonio","Santa Maria","Santa Theresa","Tambong"],
        "Mansalay": ["B. del Mundo","Balugo","Bonbon","Budburan","Cabalwa","Don Pedro","Maliwanag","Manaul","Panaytayan","Poblacion","Roma","Santa Brigida","Santa Maria","Santa Teresita","Villa Celestial","Wasig","Waygan"],
        "Naujan": ["Adrialuna","Andres Ilagan","Antipolo","Apitong","Arangin","Aurora","Bacungan","Bagong Buhay","Balite","Bancuro","Banuton","Barcenaga","Bayani","Buhangin","Caburo","Concepcion","Dao","Del Pilar","Estrella","Evangelista","Gamao","General Esco","Herrera","Inarawan","Kalinisan","Laguna","Mabini","Magtibay","Mahabang Parang","Malaya","Malinao","Malvar","Masagana","Masaguing","Melgar A","Melgar B","Metolza","Montelago","Montemayor","Motoderazo","Mulawin","Nag-iba I","Nag-iba II","Pagkakaisa","Paitan","Paniquian","Pinagsabangan I","Pinagsabangan II","Piñahan","Poblacion I","Poblacion II","Poblacion III","Sampaguita","San Agustin I","San Agustin II","San Andres","San Antonio","San Carlos","San Isidro","San Jose","San Luis","San Nicolas","San Pedro","Santa Cruz","Santa Isabel","Santa Maria","Santiago","Santo Niño","Tagumpay","Tigkan"],
        "Pinamalayan": ["Anoling","Bacungan","Bangbang","Banilad","Buli","Cacawan","Calingag","Del Razon","Guinhawa","Inclanay","Lumangbayan","Malaya","Maliangcog","Maningcol","Marayos","Marfrancisco","Nabuslot","Pagalagala","Palayan","Pambisan Malaki","Pambisan Munti","Panggulayan","Papandayan","Pili","Quinabigan","Ranzo","Rosario","Sabang","Santa Isabel","Santa Maria","Santa Rita","Santo Niño","Wawa","Zone I","Zone II","Zone III","Zone IV"],
        "Pola": ["Bacawan","Bacungan","Batuhan","Bayanan","Biga","Buhay na Tubig","Calima","Calubasanhon","Campamento","Casiligan","Malibago","Maluanluan","Matulatula","Misong","Pahilahan","Panikihan","Pula","Puting Cacao","Tagbakin","Tagumpay","Tiguihan","Zone I","Zone II"],
        "Puerto Galera": ["Aninuan","Baclayan","Balatero","Dulangan","Palangan","Poblacion","Sabang","San Antonio","San Isidro","Santo Niño","Sinandigan","Tabinay","Villaflor"],
        "Roxas": ["Bagumbayan","Cantil","Dangay","Happy Valley","Libertad","Libtong","Little Tanauan","Mabuhay","Maraska","Odiong","Paclasan","San Aquilino","San Isidro","San Jose","San Mariano","San Miguel","San Rafael","San Vicente","Uyao","Victoria"],
        "San Teodoro": ["Bigaan","Caagutayan","Calangatan","Calsapa","Ilag","Lumangbayan","Poblacion","Tacligan"],
        "Socorro": ["Bagsok","Batong Dalig","Bayuin","Bugtong na Tuog","Calocmoy","Calubayan","Catiningan","Fortuna","Happy Valley","Leuteboro I","Leuteboro II","Ma. Concepcion","Mabuhay I","Mabuhay II","Malugay","Matungao","Monteverde","Pasi I","Pasi II","Santo Domingo","Subaan","Villareal","Zone I","Zone II","Zone III","Zone IV"],
        "Victoria": ["Alcate","Antonino","Babangonan","Bagong Buhay","Bagong Silang","Bambanin","Bethel","Canaan","Concepcion","Duongan","Jose Leido Jr.","Loyal","Mabini","Macatoc","Malabo","Merit","Ordovilla","Pakyas","Poblacion I","Poblacion II","Poblacion III","Poblacion IV","Sampaguita","San Antonio","San Cristobal","San Gabriel","San Gelacio","San Isidro","San Juan","San Narciso","Urdaneta","Villa Cerveza"]
    };

    (function() {
        var municipalitySelect = document.getElementById('municipality');
        var barangaySelect = document.getElementById('barangay');
        var presetMunicipality = <?php echo json_encode($current_municipality); ?>;
        var presetBarangay = <?php echo json_encode($current_barangay); ?>;

        Object.keys(ORIENTAL_MINDORO_BARANGAYS).sort().forEach(function(name) {
            var opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            if (name === presetMunicipality) opt.selected = true;
            municipalitySelect.appendChild(opt);
        });

        function populateBarangays(selectedBarangay) {
            var barangays = ORIENTAL_MINDORO_BARANGAYS[municipalitySelect.value] || [];
            barangaySelect.innerHTML = '';

            if (!barangays.length) {
                barangaySelect.appendChild(new Option('Select Municipality first', ''));
                barangaySelect.disabled = true;
                return;
            }

            barangaySelect.disabled = false;
            barangaySelect.appendChild(new Option('Select Barangay', ''));
            barangays.forEach(function(b) {
                var opt = new Option(b, b);
                if (b === selectedBarangay) opt.selected = true;
                barangaySelect.appendChild(opt);
            });
        }

        municipalitySelect.addEventListener('change', function() { populateBarangays(''); });

        if (presetMunicipality) {
            populateBarangays(presetBarangay);
        }
    })();

    (function() {
        var hiddenLabel = document.getElementById('label');
        var buttons = document.querySelectorAll('.label-choice-btn');

        function selectLabel(val) {
            hiddenLabel.value = val;
            buttons.forEach(function(b) { b.classList.toggle('selected', b.dataset.label === val); });
        }

        buttons.forEach(function(b) {
            b.addEventListener('click', function() { selectLabel(b.dataset.label); });
        });

        selectLabel(hiddenLabel.value === 'Work' ? 'Work' : 'Home');
    })();
</script>
