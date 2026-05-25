// Script para el botón de colapsar sidebar (móvil y desktop)
document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // 1. Sidebar Toggle
    // ----------------------------------------------------
    document.querySelectorAll('[data-lte-toggle="sidebar"]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            if (window.innerWidth >= 992) {
                document.body.classList.toggle('sidebar-collapse');
                document.body.classList.remove('sidebar-open');
            } else {
                document.body.classList.toggle('sidebar-open');
                document.body.classList.remove('sidebar-collapse');
            }
        });
    });

    // Cerrar menú al hacer clic en el contenido en móvil
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 992 && document.body.classList.contains('sidebar-open')) {
            const sidebar = document.querySelector('.app-sidebar');
            const toggleBtn = document.querySelector('[data-lte-toggle="sidebar"]');
            
            // Si el clic no es dentro del sidebar ni en el botón de toggle, cerramos el sidebar
            if (sidebar && !sidebar.contains(e.target) && toggleBtn && !toggleBtn.contains(e.target)) {
                document.body.classList.remove('sidebar-open');
            }
        }
    });

    // ----------------------------------------------------
    // 2. Buscador en Tiempo Real (Módulos y Categorías)
    // ----------------------------------------------------
    const searchInput = document.getElementById('global-search');
    const accordion = document.getElementById('dashboardAccordion');

    if (searchInput && accordion) {
        const categoryItems = accordion.querySelectorAll('.category-item');
        
        // Almacenar el estado original de colapso de cada categoría
        const originalStates = [];
        categoryItems.forEach((catItem) => {
            const button = catItem.querySelector('.accordion-button');
            const collapseEl = catItem.querySelector('.accordion-collapse');
            const isExpanded = collapseEl && collapseEl.classList.contains('show');
            originalStates.push({
                elementId: collapseEl ? collapseEl.id : null,
                isExpanded: isExpanded
            });
        });

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();
            const noResultsMessage = document.getElementById('no-results-message');
            const searchTermPlaceholder = document.getElementById('search-term-placeholder');
            
            if (noResultsMessage && searchTermPlaceholder) {
                searchTermPlaceholder.textContent = searchInput.value;
            }

            let anyCategoryMatches = false;

            if (query === '') {
                // Si la búsqueda está vacía, restauramos todo al estado inicial
                categoryItems.forEach((catItem, idx) => {
                    catItem.classList.remove('d-none'); // Mostrar categoría
                    
                    const button = catItem.querySelector('.accordion-button');
                    const collapseEl = catItem.querySelector('.accordion-collapse');
                    const origState = originalStates[idx];

                    // Mostrar todos los módulos de esta categoría
                    const moduleContainers = catItem.querySelectorAll('.module-container');
                    moduleContainers.forEach(mod => mod.classList.remove('d-none'));

                    if (collapseEl && button) {
                        if (origState.isExpanded) {
                            collapseEl.classList.add('show');
                            button.classList.remove('collapsed');
                            button.setAttribute('aria-expanded', 'true');
                        } else {
                            collapseEl.classList.remove('show');
                            button.classList.add('collapsed');
                            button.setAttribute('aria-expanded', 'false');
                        }
                    }
                });

                if (noResultsMessage) {
                    noResultsMessage.classList.add('d-none');
                }
                return;
            }

            // Filtrado activo
            categoryItems.forEach((catItem) => {
                const button = catItem.querySelector('.accordion-button');
                const catTitle = button ? button.textContent.toLowerCase() : '';
                const collapseEl = catItem.querySelector('.accordion-collapse');
                
                let matchesCategoryTitle = catTitle.includes(query);
                let matchingModulesCount = 0;

                const moduleContainers = catItem.querySelectorAll('.module-container');
                moduleContainers.forEach((modContainer) => {
                    const titleEl = modContainer.querySelector('.module-title');
                    const descEl = modContainer.querySelector('.module-desc');
                    
                    const modTitle = titleEl ? titleEl.textContent.toLowerCase() : '';
                    const modDesc = descEl ? descEl.textContent.toLowerCase() : '';

                    const matchesModule = modTitle.includes(query) || modDesc.includes(query);

                    if (matchesModule) {
                        modContainer.classList.remove('d-none');
                        matchingModulesCount++;
                    } else {
                        modContainer.classList.add('d-none');
                    }
                });

                // Si coincide el título de la categoría, mostramos todos sus módulos, de lo contrario solo los que coinciden
                if (matchesCategoryTitle) {
                    moduleContainers.forEach(mod => mod.classList.remove('d-none'));
                }

                const shouldShowCategory = matchesCategoryTitle || (matchingModulesCount > 0);

                if (shouldShowCategory) {
                    catItem.classList.remove('d-none');
                    anyCategoryMatches = true;

                    // Expandir automáticamente para mostrar resultados
                    if (collapseEl && button) {
                        collapseEl.classList.add('show');
                        button.classList.remove('collapsed');
                        button.setAttribute('aria-expanded', 'true');
                    }
                } else {
                    catItem.classList.add('d-none');
                }
            });

            // Mostrar u ocultar mensaje de "No resultados"
            if (noResultsMessage) {
                if (anyCategoryMatches) {
                    noResultsMessage.classList.add('d-none');
                } else {
                    noResultsMessage.classList.remove('d-none');
                }
            }
        });
    }
});