<?php
// application/views/admin/staff/add.php
$old_input = (array) ($old_input ?? []);
$preserve_value = function($key, $default = '') use ($old_input) {
    $value = isset($old_input[$key]) ? $old_input[$key] : set_value($key, $default);
    return htmlspecialchars($value);
};
$selected_branch_id = $preserve_value('branch_id');
$last_name_value = $preserve_value('last_name');
$default_password = $last_name_value ? $last_name_value . '123' : '';
$password_value = isset($old_input['password']) ? htmlspecialchars($old_input['password']) : set_value('password', $default_password);
?>
<div class="container-fluid py-4 fade-in">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1a2e;">Add New Staff</h4>
            <small class="text-muted">Create a new staff account and assign branch access.</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= site_url('admin/staff'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Staff
            </a>
        </div>
    </div>

    <div class="card staff-form-card">
        <div class="card-body">
            <div class="page-section" style="margin-top:0;">
                <span class="section-title"><i class="fas fa-id-card me-2"></i>Staff Details</span>
                <hr>
            </div>
            <small class="text-muted d-block mb-3">Enter contact information and access settings for the staff member.</small>
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= $preserve_value('first_name'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= $preserve_value('middle_name'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= $last_name_value; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $preserve_value('email'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="contact_number">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?= $preserve_value('contact_number'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="city">City / Municipality</label>
                            <select class="form-control" id="city" name="city">
                                <option value="">Select City / Municipality</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="barangay">Barangay</label>
                            <select class="form-control" id="barangay" name="barangay" disabled>
                                <option value="">Select City / Municipality first</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="street">Street <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="street" name="street" value="<?= $preserve_value('street'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="branch_id">Assigned Branch <span class="text-danger">*</span></label>
                            <select class="form-control" id="branch_id" name="branch_id" required>
                                <option value="" disabled <?= $selected_branch_id === '' ? 'selected' : ''; ?>>Select Branch</option>
                                <?php foreach (($branches ?? []) as $b): ?>
                                    <option value="<?= $b['branch_id']; ?>" <?= $selected_branch_id == $b['branch_id'] ? 'selected' : ''; ?>><?= htmlspecialchars($b['branch_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="text" class="form-control" id="password" name="password" value="<?= $password_value; ?>" placeholder="Default: last name + 123" minlength="6">
                        </div>
                    </div>
                </div>

                <div class="staff-form-note">
                    <i class="fas fa-info-circle text-primary"></i>
                    <span class="ms-2">Default password is generated from the staff member's last name plus <code>123</code>. You can overwrite this before saving.</span>
                </div>

                <div class="staff-form-actions">
                    <button type="submit" class="btn btn-primary">Create Staff</button>
                    <a href="<?= site_url('admin/staff'); ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var ORIENTAL_MINDORO_BARANGAYS = {
        "Calapan City": ["Balingayan","Balite","Baruyan","Batino","Bayanan I","Bayanan II","Biga","Bondoc","Bucayao","Buhuan","Bulusan","Calero","Camansihan","Camilmil","Canubing I","Canubing II","Comunal","Guinobatan","Gulod","Gutad","Ibaba East","Ibaba West","Ilaya","Lalud","Lazareto","Libis","Lumangbayan","Mahal na Pangalan","Maidlang","Malad","Malamig","Managpi","Masipit","Nag-iba I","Nag-iba II","Navotas","Pachoca","Palhi","Panggalaan","Parang","Patas","Personas","Putingtubig","Salong","San Antonio","San Vicente Central","San Vicente East","San Vicente North","San Vicente South","San Vicente West","Santa Cruz","Santa Isabel","Santa Maria Village","Santa Rita","Santo Niño","Sapul","Silonay","Suqui","Tawagan","Tawiran","Tibag","Wawa"],
        "Baco": ["Alag","Bangkatan","Baras","Bayanan","Burbuli","Catwiran I","Catwiran II","Dulangan I","Dulangan II","Lantuyang","Lumangbayan","Malapad","Mangangan I","Mangangan II","Mayabig","Pambisan","Poblacion","Pulang-Tubig","Putican-Cabulo","San Andres","San Ignacio","Santa Cruz","Santa Rosa I","Santa Rosa II","Tabon-tabon","Tagumpay","Water"],
        "Bansud": ["Alcadesma","Bato","Conrazon","Malo","Manihala","Pag-asa","Poblacion","Proper Bansud","Proper Tiguisan","Rosacara","Salcedo","Sumagui","Villa Pag-asa"],
        "Bongabong": ["Anilao","Aplaya","Bagumbayan I","Bagumbayan II","Batangan","Bukal","Camantigue","Carmundo","Cawayan","Dayhagan","Formon","Hagan","Hagupit","Ipil","Kaligtasan","Labasan","Labonan","Libertad","Lisap","Luna","Malitbog","Mapang","Masaguisi","Mina de Oro","Morente","Ogbot","Orconuma","Poblacion","Polusahi","Sagana","San Isidro","San Jose","San Juan","Santa Cruz","Sigange","Tawas"],
        "Bulalacao": ["Bagong Sikat","Balatasan","Benli","Cabugao","Cambunang","Campaasan","Maasin","Maujao","Milagrosa","Nasukob","Poblacion","San Francisco","San Isidro","San Juan","San Roque"],
        "Gloria": ["Agos","Agsalin","Alma Villa","Andres Bonifacio","Balete","Banus","Banutan","Bulaklakan","Buong Lupa","Gaudencio Antonino","Guimbonan","Kawit","Lucio Laurel","Macario Adriatico","Malamig","Malayong","Maligaya","Malubay","Manguyang","Maragooc","Mirayan","Narra","Papandungin","San Antonio","Santa Maria","Santa Theresa","Tambong"],
        "Mansalay": ["B. del Mundo","Balugo","Bonbon","Budburan","Cabalwa","Don Pedro","Maliwanag","Manaul","Panaytayan","Poblacion","Roma","Santa Brigida","Santa Maria","Santa Teresita","Villa Celestial","Wasig","Waygan"],
        "Naujan": ["Adrialuna","Andres Ilagan","Antipolo","Apitong","Arangin","Aurora","Bacungan","Bagong Buhay","Balite","Bancuro","Banuton","Barcenaga","Bayani","Buhangin","Caburo","Concepcion","Curva","Dao","Del Pilar","Estrella","Evangelista","Gamao","General Esco","Herrera","Inarawan","Kalinisan","Laguna","Mabini","Magtibay","Mahabang Parang","Malaya","Malinao","Malvar","Masagana","Masaguing","Melgar A","Melgar B","Metolza","Montelago","Montemayor","Motoderazo","Mulawin","Nag-iba I","Nag-iba II","Pagkakaisa","Paitan","Paniquian","Pinagsabangan I","Pinagsabangan II","Piñahan","Poblacion I","Poblacion II","Poblacion III","Sampaguita","San Agustin I","San Agustin II","San Andres","San Antonio","San Carlos","San Isidro","San Jose","San Luis","San Nicolas","San Pedro","Santa Cruz","Santa Isabel","Santa Maria","Santiago","Santo Niño","Tagumpay","Tigkan"],
        "Pinamalayan": ["Anoling","Bacungan","Bangbang","Banilad","Buli","Cacawan","Calingag","Del Razon","Guinhawa","Inclanay","Lumangbayan","Malaya","Maliangcog","Maningcol","Marayos","Marfrancisco","Nabuslot","Pagalagala","Palayan","Pambisan Malaki","Pambisan Munti","Panggulayan","Papandayan","Pili","Quinabigan","Ranzo","Rosario","Sabang","Santa Isabel","Santa Maria","Santa Rita","Santo Niño","Wawa","Zone I","Zone II","Zone III","Zone IV"],
        "Pola": ["Bacawan","Bacungan","Batuhan","Bayanan","Biga","Buhay na Tubig","Calima","Calubasanhon","Campamento","Casiligan","Malibago","Maluanluan","Matulatula","Misong","Pahilahan","Panikihan","Pula","Puting Cacao","Tagbakin","Tagumpay","Tiguihan","Zone I","Zone II"],
        "Puerto Galera": ["Aninuan","Baclayan","Balatero","Dulangan","Palangan","Poblacion","Sabang","San Antonio","San Isidro","Santo Niño","Sinandigan","Tabinay","Villaflor"],
        "Roxas": ["Bagumbayan","Cantil","Dangay","Happy Valley","Libertad","Libtong","Little Tanauan","Mabuhay","Maraska","Odiong","Paclasan","San Aquilino","San Isidro","San Jose","San Mariano","San Miguel","San Rafael","San Vicente","Uyao","Victoria"],
        "San Teodoro": ["Bigaan","Caagutayan","Calangatan","Calsapa","Ilag","Lumangbayan","Poblacion","Tacligan"],
        "Socorro": ["Bagsok","Batong Dalig","Bayuin","Bugtong na Tuog","Calocmoy","Calubayan","Catiningan","Fortuna","Happy Valley","Leuteboro I","Leuteboro II","Ma. Concepcion","Mabuhay I","Mabuhay II","Malugay","Matungao","Monteverde","Pasi I","Pasi II","Santo Domingo","Subaan","Villareal","Zone I","Zone II","Zone III","Zone IV"],
        "Victoria": ["Alcate","Antonino","Babangonan","Bagong Buhay","Bagong Silang","Bambanin","Bethel","Canaan","Concepcion","Duongan","Jose Leido Jr.","Loyal","Mabini","Macatoc","Malabo","Merit","Ordovilla","Pakyas","Poblacion I","Poblacion II","Poblacion III","Poblacion IV","Sampaguita","San Antonio","San Cristobal","San Gabriel","San Gelacio","San Isidro","San Juan","San Narciso","Urdaneta","Villa Cerveza"]
    };

    (function() {
        var citySelect = document.getElementById('city');
        var barangaySelect = document.getElementById('barangay');

        var selectedCity = <?= json_encode(isset($old_input['city']) ? $old_input['city'] : set_value('city', '')); ?>;
        Object.keys(ORIENTAL_MINDORO_BARANGAYS).sort().forEach(function(name) {
            var opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            if (name === selectedCity) opt.selected = true;
            citySelect.appendChild(opt);
        });

        function populateBarangays(selectedBarangay) {
            var barangays = ORIENTAL_MINDORO_BARANGAYS[citySelect.value] || [];
            barangaySelect.innerHTML = '';

            if (!barangays.length) {
                barangaySelect.appendChild(new Option('Select City / Municipality first', ''));
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

        citySelect.addEventListener('change', function() { populateBarangays(''); });

        if (citySelect.value) {
            var selectedBarangay = <?= json_encode(isset($old_input['barangay']) ? $old_input['barangay'] : set_value('barangay', '')); ?>;
            populateBarangays(selectedBarangay);
        }

        var lastNameInput = document.getElementById('last_name');
        var passwordInput = document.getElementById('password');

        function updateDefaultPassword() {
            var lastName = lastNameInput.value.trim();
            var currentPass = passwordInput.value;
            var generatedPass = passwordInput.getAttribute('data-autogenerated');

            if (!lastName) {
                if (generatedPass) {
                    passwordInput.value = '';
                    passwordInput.removeAttribute('data-autogenerated');
                }
                return;
            }

            var defaultPass = lastName + '123';
            if (currentPass === '' || currentPass === generatedPass || currentPass === defaultPass) {
                passwordInput.value = defaultPass;
                passwordInput.setAttribute('data-autogenerated', defaultPass);
            }
        }

        lastNameInput.addEventListener('input', updateDefaultPassword);
        passwordInput.addEventListener('focus', function() {
            if (!passwordInput.value && lastNameInput.value.trim()) {
                updateDefaultPassword();
            }
        });
        passwordInput.addEventListener('input', function() {
            if (passwordInput.value !== passwordInput.getAttribute('data-autogenerated')) {
                passwordInput.removeAttribute('data-autogenerated');
            }
        });

        updateDefaultPassword();
    })();
</script>
