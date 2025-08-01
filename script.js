// Módulo Issabel - JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo Issabel cargado correctamente');
    
    // Funcionalidad básica del módulo
    const content = document.querySelector('.content');
    
    if (content) {
        content.addEventListener('click', function() {
            this.style.backgroundColor = this.style.backgroundColor === 'rgb(52, 152, 219)' ? '#f4f4f4' : '#3498db';
            this.style.color = this.style.color === 'white' ? '#333' : 'white';
        });
    }
});