(function () {
    'use strict';

    function openActivityDetail(button) {
        const modal = document.getElementById('activityDetailModal');
        if (!modal) return;
        let data = {};
        try { data = JSON.parse(atob(button.dataset.activity || '')) || {}; }
        catch (error) { console.error('Gagal membaca detail aktivitas:', error); return; }

        const fields = {
            detailTime: data.time || '-',
            detailUser: data.user || '-',
            detailAction: data.action || '-',
            detailModule: data.module || '-',
            detailDescription: data.description || '-',
        };
        Object.entries(fields).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function closeActivityDetail() {
        const modal = document.getElementById('activityDetailModal');
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('click', function (event) {
        const detailButton = event.target.closest('[data-activity-detail]');
        if (detailButton) return openActivityDetail(detailButton);
        if (event.target.closest('[data-close-activity-modal]')) return closeActivityDetail();
        const modal = document.getElementById('activityDetailModal');
        if (modal && event.target === modal) closeActivityDetail();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeActivityDetail();
    });

    window.showActivityDetail = openActivityDetail;
    window.closeActivityDetail = closeActivityDetail;
})();
