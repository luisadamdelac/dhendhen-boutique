<style>
    .history-container { margin-top: 30px; max-width: 700px; }

    .history-item {
        background: white;
        padding: 16px 22px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .history-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .history-icon.order {
        background: var(--primary-pink-light, #ffe3f0);
        color: var(--primary-pink-dark, #d6336c);
    }

    .history-icon.account {
        background: #e0f2fe;
        color: #0369a1;
    }

    .history-text h3 {
        font-size: 15px;
        font-weight: 600;
        color: var(--dark-gray, #333);
        margin-bottom: 2px;
    }

    .history-text p {
        font-size: 13px;
        color: var(--gray-600, #666);
        margin: 0;
    }

    .history-date {
        margin-left: auto;
        font-size: 12px;
        color: var(--gray-400, #999);
        white-space: nowrap;
        text-align: right;
    }

    .history-item a.history-link {
        text-decoration: none;
        color: inherit;
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
    }
</style>

<h1><i class="fas fa-history"></i> Activity History</h1>
<p style="color:var(--gray-600,#666); margin-bottom:0;">Your recent orders and account changes, all in one place.</p>

<div class="history-container">
    <?php if (!empty($events)): ?>
        <?php foreach ($events as $event): ?>
            <div class="history-item">
                <?php if ($event['link']): ?><a href="<?php echo $event['link']; ?>" class="history-link"><?php endif; ?>
                    <div class="history-icon <?php echo $event['type']; ?>">
                        <i class="fas fa-<?php echo $event['type'] === 'order' ? 'box' : 'user-circle'; ?>"></i>
                    </div>
                    <div class="history-text">
                        <h3><?php echo htmlspecialchars($event['label']); ?></h3>
                        <?php if (!empty($event['detail'])): ?>
                            <p><?php echo htmlspecialchars($event['detail']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php if ($event['link']): ?></a><?php endif; ?>
                <div class="history-date"><?php echo date('M d, Y g:i A', strtotime($event['date'])); ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="history-item" style="justify-content:center; color:var(--gray-600,#666);">
            No activity yet.
        </div>
    <?php endif; ?>
</div>
