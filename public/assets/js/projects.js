// ============================================================
// Projects Page — Load More (AJAX)
// ============================================================

(function ($) {
  'use strict';

  let currentOffset = 6; // Already loaded 6
  let isLoading     = false;
  let hasMore       = true;

  const $loadMoreBtn = $('#btn-load-more');
  const $grid        = $('#public-projects-grid');

  function getLabelClass(label) {
    const map = {
      'AI':      'label-ai',
      'Web App': 'label-webapp',
      'SaaS':    'label-saas',
      'IoT':     'label-iot',
      'Mobile':  'label-mobile',
      'Other':   'label-other',
    };
    return map[label] || 'label-other';
  }

  function renderCard(project) {
    const imgHtml = project.cover_image
      ? `<img src="${escapeHtml(project.cover_image)}" alt="${escapeHtml(project.title)}" class="project-card-img" loading="lazy">`
      : `<div class="project-card-img-placeholder"><svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg></div>`;

    const labelClass = getLabelClass(project.label);
    const detailUrl  = FAY_PUBLIC.projectsBase + '/' + project.slug;

    return `
      <a href="${detailUrl}" class="project-card">
        ${imgHtml}
        <div class="project-card-body">
          <div class="project-card-meta">
            <span class="label-badge ${labelClass}">${escapeHtml(project.label)}</span>
            <span style="color: var(--text-muted); font-size: 12px;">${project.project_year || ''}</span>
          </div>
          <div class="project-card-title">${escapeHtml(project.title)}</div>
          <div class="project-card-desc">${escapeHtml(project.description)}</div>
          <div class="project-card-footer">
            <span class="project-card-link">
              View Project
              <svg viewBox="0 0 16 16" width="12" height="12"><path fill="currentColor" d="M6.22 3.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L9.94 8 6.22 4.28a.75.75 0 010-1.06z"/></svg>
            </span>
          </div>
        </div>
      </a>
    `;
  }

  function loadMore() {
    if (isLoading || !hasMore) return;
    isLoading = true;

    $loadMoreBtn.html('<span class="spinner spinner-dark"></span> Loading more projects...')
                .prop('disabled', true);

    $.ajax({
      url: FAY_PUBLIC.apiBase + '/public/load-projects.php?offset=' + currentOffset,
      method: 'GET',
      success: function (res) {
        if (res.success && res.data.projects.length > 0) {
          res.data.projects.forEach(project => {
            $grid.append($(renderCard(project)).hide().fadeIn(300));
          });

          currentOffset = res.data.offset;
          hasMore       = res.data.has_more;

          if (!hasMore) {
            $loadMoreBtn.closest('.load-more-wrapper').fadeOut();
          } else {
            $loadMoreBtn.html('Load More').prop('disabled', false);
          }
        } else {
          $loadMoreBtn.closest('.load-more-wrapper').fadeOut();
        }
      },
      error: function () {
        $loadMoreBtn.html('Load More').prop('disabled', false);
      },
      complete: function () {
        isLoading = false;
      },
    });
  }

  function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  $(document).ready(function () {
    $loadMoreBtn.on('click', loadMore);
  });

}(jQuery));
