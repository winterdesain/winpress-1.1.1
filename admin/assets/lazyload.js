document.addEventListener("DOMContentLoaded", function () {
    var images = document.querySelectorAll('img[data-src], iframe[data-src]');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function(entries, obs){
            entries.forEach(function(entry){
                if (entry.isIntersecting) {
                    var el = entry.target;
                    if (el.dataset.src) el.src = el.dataset.src;
                    if (el.dataset.srcset) el.srcset = el.dataset.srcset;
                    obs.unobserve(el);
                }
            });
        }, {rootMargin: '50px 0px', threshold: 0.01});
        images.forEach(function(i){ io.observe(i); });
    } else {
        images.forEach(function(i){
            if (i.dataset.src) i.src = i.dataset.src;
        });
    }
});
