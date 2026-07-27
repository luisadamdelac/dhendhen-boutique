<!-- Staff Activity Log -->
<style>
.al-toolbar {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
    gap: 12px; padding: 1.1rem 1.25rem;
}
.al-search { position: relative; flex: 1 1 260px; max-width: 320px; }
.al-search i {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    color: #b0b3c6; font-size: 13px; pointer-events: none;
}
.al-search input {
    width: 100%; border: 1px solid var(--border); border-radius: 999px;
    padding: .55rem 1rem .55rem 2.2rem; font-size: .85rem; background: #fbfbfe;
}
.al-search input:focus { outline: none; border-color: var(--primary-pink); background: #fff; box-shadow: 0 0 0 3px rgba(255,105,180,.12); }

.al-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.al-date-range {
    display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--border);
    border-radius: 999px; padding: .5rem 1rem; font-size: .82rem; font-weight: 600; color: var(--text);
    background: #fff; cursor: pointer; position: relative;
}
.al-date-range i.fa-calendar { color: var(--primary-pink-dark); }
.al-date-range i.fa-chevron-down { font-size: 10px; color: #b0b3c6; }
.al-date-panel {
    display: none; position: absolute; top: calc(100% + 8px); right: 0; z-index: 20;
    background: #fff; border: 1px solid var(--border); border-radius: 14px;
    box-shadow: 0 20px 50px rgba(15,23,42,.12); padding: 14px; min-width: 240px; cursor: default;
}
.al-date-panel.show { display: block; }
.al-date-panel label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--gray); display: block; margin-bottom: 4px; }
.al-date-panel input[type="date"] { width: 100%; border: 1px solid var(--border); border-radius: 8px; padding: .4rem .6rem; font-size: .82rem; margin-bottom: 10px; }
.al-date-panel .al-date-apply-row { display: flex; gap: 8px; }

.al-export-btn {
    display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--primary-pink-dark);
    color: var(--primary-pink-dark); background: #fff; border-radius: 999px; padding: .5rem 1.1rem;
    font-size: .82rem; font-weight: 700;
}
.al-export-btn:hover { background: var(--primary-pink-light); color: var(--primary-pink-dark); }

.al-table thead th {
    background: #fdeef4; color: #4a2036; border-bottom: none;
    font-size: 11px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700;
    padding: 14px 20px;
}
.al-table td { padding: 14px 20px; vertical-align: middle; border-color: #f4f6fb; }
.al-table tbody tr { cursor: default; transition: background .15s ease; }
.al-table tbody tr:hover { background: #fafbff; }

.al-icon-box {
    width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center; font-size: 13px;
}
.al-date-cell { display: flex; align-items: center; gap: 10px; }
.al-date-cell .al-day { font-weight: 600; color: #1a1a2e; font-size: 13px; }
.al-date-cell .al-time { font-size: 11px; color: var(--gray); }
.al-action-cell { display: flex; align-items: center; gap: 10px; }
.al-action-label { font-weight: 700; font-size: 13px; color: #1a1a2e; }
.al-chevron { color: var(--primary-pink-dark); font-size: 13px; opacity: .7; }

.al-footer {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
    gap: 12px; padding: 1rem 1.25rem;
}
.al-footer-info { font-size: .82rem; color: var(--gray); }
.al-pager { display: flex; align-items: center; gap: 6px; }
.al-pager a, .al-pager span {
    width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border);
    display: inline-flex; align-items: center; justify-content: center; font-size: .82rem;
    color: var(--text); text-decoration: none;
}
.al-pager a.disabled { opacity: .4; pointer-events: none; }
.al-pager span.active, .al-pager a.active {
    background: var(--gradient-primary); border-color: transparent; color: #fff; font-weight: 700;
}
</style>

<div class="container-fluid py-4 fade-in">
    <div class="ds-hero-card mb-3">
        <div class="ds-hero-banner">
            <svg class="ds-hero-wave" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,110 C240,170 480,50 720,90 C960,130 1200,50 1440,100 L1440,200 L0,200 Z" fill="rgba(255,105,180,0.16)"></path>
                <path d="M0,140 C280,80 560,180 840,120 C1080,70 1280,140 1440,130 L1440,200 L0,200 Z" fill="rgba(233,30,99,0.22)"></path>
            </svg>
            <div class="ds-hero-banner-content">
                <h4 class="fw-bold mb-1" style="color:#1a1a2e;"><i class="fas fa-history"></i> Activity Log</h4>
                <small class="text-muted">A record of the actions you've taken — order updates, stock changes, and profile edits.</small>
            </div>
        </div>
    </div>

    <div class="card ds-pink-table-card">

        <div class="al-toolbar">
            <div class="al-search">
                <i class="fas fa-search"></i>
                <input type="text" id="alSearchInput" placeholder="Search activity…">
            </div>
            <div class="al-actions">
                <div class="al-date-range" id="alDateRangeToggle">
                    <i class="fas fa-calendar"></i>
                    <span id="alDateRangeLabel">Select date range</span>
                    <i class="fas fa-chevron-down"></i>
                    <div class="al-date-panel" id="alDatePanel">
                        <label for="alDateFrom">From</label>
                        <input type="date" id="alDateFrom">
                        <label for="alDateTo">To</label>
                        <input type="date" id="alDateTo">
                        <div class="al-date-apply-row">
                            <button type="button" class="btn btn-sm btn-primary flex-fill" id="alDateApply">Apply</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="alDateClear">Clear</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="al-export-btn" id="alExportBtn">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table al-table mb-0" id="alTable">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th class="text-end pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $ts = strtotime($log['created_at']);
                                $action = $log['action'] ?? '';
                                $actionLabel = $action;
                                $icon = 'fa-bolt';
                                $iconColor = '#6a3fd6';
                                $iconBg = '#eee8ff';
                                if (strpos($action, 'login') !== false) {
                                    $icon = 'fa-right-to-bracket'; $iconColor = '#1565c0'; $iconBg = '#e3f2fd';
                                } elseif (strpos($action, 'dashboard') !== false) {
                                    $icon = 'fa-chart-column'; $iconColor = '#e0559c'; $iconBg = '#ffe3ee';
                                } elseif (strpos($action, 'stock') !== false) {
                                    $icon = 'fa-boxes-stacked'; $iconColor = '#2e7d32'; $iconBg = '#e8f5e9';
                                } elseif (strpos($action, 'order') !== false) {
                                    $icon = 'fa-shopping-cart'; $iconColor = '#f57f17'; $iconBg = '#fff8e1';
                                } elseif (strpos($action, 'profile') !== false) {
                                    $icon = 'fa-user-pen'; $iconColor = '#3949ab'; $iconBg = '#e8eaf6';
                                }
                            ?>
                            <tr data-date="<?= date('Y-m-d', $ts); ?>"
                                data-search="<?= htmlspecialchars(strtolower($action . ' ' . ($log['details'] ?? ''))); ?>">
                                <td>
                                    <div class="al-date-cell">
                                        <div class="al-icon-box" style="background:#ffe3ee;color:var(--primary-pink-dark);"><i class="fas fa-calendar-days"></i></div>
                                        <div>
                                            <div class="al-day"><?= date('M d, Y', $ts); ?></div>
                                            <div class="al-time"><?= date('h:i:s A', $ts); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="al-action-cell">
                                        <div class="al-icon-box" style="background:<?= $iconBg; ?>;color:<?= $iconColor; ?>;"><i class="fas <?= $icon; ?>"></i></div>
                                        <span class="al-action-label"><?= htmlspecialchars($actionLabel); ?></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($log['details'] ?? '—'); ?></td>
                                <td class="text-end pe-4"><i class="fas fa-chevron-right al-chevron"></i></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No activity logged yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
            $total = $total ?? count($logs ?? []);
            $page = $page ?? 1;
            $pages = $pages ?? 1;
            $perPage = 20;
            $rangeStart = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
            $rangeEnd = min($page * $perPage, $total);
        ?>
        <div class="al-footer">
            <div class="al-footer-info" id="alShowingLabel">
                Showing <?= $rangeStart; ?> to <?= $rangeEnd; ?> of <?= $total; ?> entries
            </div>
            <?php if ($pages > 1): ?>
                <div class="al-pager">
                    <a class="<?= $page <= 1 ? 'disabled' : ''; ?>" href="<?= site_url('staff/profile/activity_log?page=' . max(1, $page - 1)); ?>">
                        <i class="fas fa-chevron-left" style="font-size:11px;"></i>
                    </a>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?= $i; ?></span>
                        <?php else: ?>
                            <a href="<?= site_url('staff/profile/activity_log?page=' . $i); ?>"><?= $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <a class="<?= $page >= $pages ? 'disabled' : ''; ?>" href="<?= site_url('staff/profile/activity_log?page=' . min($pages, $page + 1)); ?>">
                        <i class="fas fa-chevron-right" style="font-size:11px;"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
(function () {
    const searchInput = document.getElementById('alSearchInput');
    const table = document.getElementById('alTable');
    const rows = () => Array.from(table.querySelectorAll('tbody tr[data-search]'));
    const dateToggle = document.getElementById('alDateRangeToggle');
    const datePanel = document.getElementById('alDatePanel');
    const dateFrom = document.getElementById('alDateFrom');
    const dateTo = document.getElementById('alDateTo');
    const dateLabel = document.getElementById('alDateRangeLabel');

    let activeFrom = '', activeTo = '';

    function applyFilters() {
        const term = (searchInput.value || '').trim().toLowerCase();
        rows().forEach(row => {
            const matchesSearch = !term || row.dataset.search.includes(term);
            const rowDate = row.dataset.date;
            const matchesDate = (!activeFrom || rowDate >= activeFrom) && (!activeTo || rowDate <= activeTo);
            row.style.display = (matchesSearch && matchesDate) ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', applyFilters);

    dateToggle?.addEventListener('click', function (e) {
        if (e.target.closest('.al-date-panel')) return;
        datePanel.classList.toggle('show');
    });
    document.addEventListener('click', function (e) {
        if (!dateToggle.contains(e.target)) datePanel.classList.remove('show');
    });

    document.getElementById('alDateApply')?.addEventListener('click', function () {
        activeFrom = dateFrom.value || '';
        activeTo = dateTo.value || '';
        dateLabel.textContent = (activeFrom || activeTo)
            ? (activeFrom || '…') + ' – ' + (activeTo || '…')
            : 'Select date range';
        datePanel.classList.remove('show');
        applyFilters();
    });

    document.getElementById('alDateClear')?.addEventListener('click', function () {
        dateFrom.value = '';
        dateTo.value = '';
        activeFrom = '';
        activeTo = '';
        dateLabel.textContent = 'Select date range';
        applyFilters();
    });

    document.getElementById('alExportBtn')?.addEventListener('click', function () {
        const visibleRows = rows().filter(r => r.style.display !== 'none');
        if (!visibleRows.length) { return; }

        const csvRows = [['Date/Time', 'Action', 'Details']];
        visibleRows.forEach(row => {
            const day = row.querySelector('.al-day')?.textContent.trim() || '';
            const time = row.querySelector('.al-time')?.textContent.trim() || '';
            const action = row.querySelector('.al-action-label')?.textContent.trim() || '';
            const details = row.children[2]?.textContent.trim() || '';
            csvRows.push([day + ' ' + time, action, details]);
        });

        const csvContent = csvRows.map(r => r.map(v => '"' + v.replace(/"/g, '""') + '"').join(',')).join('\r\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'activity-log.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });
})();
</script>
