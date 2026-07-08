(function(){
    function getApiBase(){
        // BASE_URL is defined in footer/header in this app; fallback hardcoded to current origin
        return (typeof BASE_URL !== 'undefined') ? BASE_URL : '/E_Benta_dropshipping_System/reseller/';
    }

    function ensureBadge(badgeId){
        var badge = document.getElementById(badgeId);
        return badge;
    }

    function renderCount(count){
        var badge = ensureBadge('notificationBadge');
        if(!badge) return;
        badge.textContent = count;
        badge.style.display = (count && Number(count) > 0) ? 'inline-flex' : 'none';
    }

    function fetchNotifications(){
        var badge = ensureBadge('notificationBadge');
        // If header doesn't have badge, don't poll
        if(!badge) return;

        var apiBase = getApiBase();
        var apiUrl = apiBase + 'index.php/api/notifications?scope=reseller';

        fetch(apiUrl, { method: 'GET' })
            .then(function(res){ 
                if (!res.ok) {
                    throw new Error('HTTP error! status: ' + res.status);
                }
                var contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                return res.json(); 
            })
            .then(function(data){
                if(data && data.success){
                    renderCount(data.count || 0);
                }
            })
            .catch(function(error){ 
                console.warn('Notification fetch failed:', error);
                /* silent failure */
            });
    }

    document.addEventListener('DOMContentLoaded', function(){
        // initial fetch
        fetchNotifications();
        // poll every 20 seconds
        setInterval(fetchNotifications, 20000);
    });
})();

