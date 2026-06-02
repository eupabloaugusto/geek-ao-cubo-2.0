/**
 * Atom: Embed Video JS (embed-video)
 *
 * Substitui o placeholder leve pelo iframe do YouTube sob demanda (click-to-play).
 * Otimiza a performance Lighthouse/LCP reduzindo arquivos JS e CSS pesados da Google.
 */

function mm_load_video_iframe(containerId, videoId, title) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Cria a tag iframe dinamicamente com autoplay ativado e rel=0 para recomendados no canal
    const iframe = document.createElement('iframe');
    iframe.setAttribute('src', `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`);
    iframe.setAttribute('title', title);
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
    iframe.setAttribute('allowfullscreen', 'true');
    iframe.className = 'embed-video__iframe';

    // Limpa o placeholder e injeta o iframe
    container.innerHTML = '';
    container.appendChild(iframe);
}
