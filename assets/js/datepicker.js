// Attend que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    // Si le navigateur est Firefox
    if (navigator.userAgent.search("Firefox") > -1) {
        // Pour chaque input de type datetime-local qui possède la classe no-help...
        document.querySelectorAll('input[type="datetime-local"]').forEach(function(input, index) {
            if (input.classList.contains('no-help')) {
                return;
            }
            // Ajoute un message précisant que Firefox ne supporte pas la sélection de l'heure
            input.setAttribute('aria-describedby', 'helpText' + index);
            let helpText = document.createElement('div');
            helpText.id = 'helpText' + index;
            helpText.className = 'form-text';
            helpText.textContent = 'It seems that Firefox does not support the time selection. This date would be saved at 00:00';
            input.parentNode.appendChild(helpText);
        });
    }
});