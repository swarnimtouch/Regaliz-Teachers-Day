import './bootstrap';
import DataTable from 'datatables.net-dt';
import { toPng } from 'html-to-image';

window.cardToPng = toPng;

document.addEventListener('DOMContentLoaded', () => {
    const initialiseTables = root => root.querySelectorAll('.admin-datatable').forEach(table => {
        if (table.querySelector('.admin-table-empty-row')) {
            return;
        }

        new DataTable(table, {
            paging: false,
            searching: false,
            info: false,
            order: [],
            language: { emptyTable: 'No records found' },
        });
    });

    initialiseTables(document);

    document.querySelectorAll('[data-live-admin-filters]').forEach(form => {
        const results = document.querySelector('[data-admin-results]');
        const status = form.parentElement.querySelector('.admin-live-status');
        const search = form.querySelector('[name="search"]');
        const exportLink = form.querySelector('.export-action');
        let debounceTimer;
        let activeRequest;

        const refresh = async pageUrl => {
            activeRequest?.abort();
            const request = new AbortController();
            activeRequest = request;
            const url = new URL(pageUrl || form.action || window.location.href, window.location.origin);
            const filters = new URLSearchParams(new FormData(form));
            if (!pageUrl) url.search = filters.toString();
            if (exportLink) {
                const exportUrl = new URL(exportLink.href);
                exportUrl.search = filters.toString();
                exportLink.href = exportUrl.toString();
            }
            results.classList.add('is-loading');
            status.textContent = 'Loading records...';

            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: request.signal,
                });
                if (!response.ok) throw new Error('Unable to load records.');
                const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                const replacement = page.querySelector('[data-admin-results]');
                if (!replacement) throw new Error('Invalid records response.');
                results.innerHTML = replacement.innerHTML;
                initialiseTables(results);
                status.textContent = '';
            } catch (error) {
                if (error.name !== 'AbortError') status.textContent = 'Could not refresh records. Please try again.';
            } finally {
                if (activeRequest === request) results.classList.remove('is-loading');
            }
        };

        form.addEventListener('submit', event => {
            event.preventDefault();
            refresh();
        });
        search?.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => refresh(), 350);
        });
        form.querySelectorAll('select, input[type="date"]').forEach(field => field.addEventListener('change', () => refresh()));
        results.addEventListener('click', event => {
            const link = event.target.closest('.pagination a');
            if (!link) return;
            event.preventDefault();
            refresh(link.href);
        });
    });
});
