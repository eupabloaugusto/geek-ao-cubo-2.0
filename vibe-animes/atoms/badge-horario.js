/**
 * Atom: Badge de Horário (badge-horario.js)
 * 
 * Este script localiza todos os badges de horário que possuem o atributo 'data-horario-utc',
 * que contém uma data no formato ISO 8601 UTC (ex: 2000-01-01T15:00:00Z).
 * Em seguida, converte a string para o fuso horário local do navegador do usuário e atualiza o texto.
 */
document.addEventListener('DOMContentLoaded', () => {
    const badges = document.querySelectorAll('.badge-horario[data-horario-utc]');
    if (!badges.length) return;
    
    badges.forEach(badge => {
        const utcString = badge.getAttribute('data-horario-utc');
        if (!utcString) return;
        
        try {
            const date = new Date(utcString);
            
            // Verifica se a data é válida
            if (isNaN(date.getTime())) return;
            
            // Extrai apenas a hora e os minutos, forçando o formato 24h ou local
            const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            // Atualiza o texto visual
            const textNode = badge.querySelector('.badge-horario__text');
            if (textNode) {
                textNode.textContent = timeStr;
            }

            // Atualiza o atributo title para acessibilidade
            badge.setAttribute('title', `Episódio às ${timeStr} (Horário Local)`);
        } catch (e) {
            console.error('Erro ao converter fuso horário do badge-horario:', e);
        }
    });
});
