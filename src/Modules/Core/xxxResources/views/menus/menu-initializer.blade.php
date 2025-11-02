<div>{{-- - Dummy No view only intialzation-- --}}</div>


@script
    <script>
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('contentArea');
        const toggleBtn = document.getElementById('toggleSidebar');
        const toggleIcon = document.getElementById('toggleIcon');

        // Global state variables.
        let states = []; // Array of available states.
        let currentStateIndex = 0;
        let sidebarMenuData = []; // To hold data from Livewire event

        // Set mode based on window width.
        // - Desktop (≥993px): cycle through ['full','icons','hidden'] (default: full).
        // - Tablet (577–992px) & Mobile (≤576px): cycle through ['hidden','icons'].
        function setMode() {
            const width = window.innerWidth;
            if (width >= 993) {
                states = ['full', 'icons', 'hidden'];
                currentStateIndex = 0; // Default to full.
            } else if (width >= 577) {
                states = ['hidden', 'icons'];
                currentStateIndex = 1; // Default to icons.
            } else {
                states = ['hidden', 'icons'];
                currentStateIndex = 0; // Default to hidden.
            }
        }

        // Return sidebar width based on the current state.
        function getSidebarWidth() {
            const state = states[currentStateIndex];
            if (state === 'full') return 250;
            if (state === 'icons') return 80;
            return 0; // hidden state.
        }

        // Update sidebar class, main content margin, and toggle button appearance.
        function updateUI() {
            // Update sidebar state.
            sidebar.classList.remove('hidden', 'icons', 'full');
            sidebar.classList.add(states[currentStateIndex]);

            // Set main content margin to exactly match sidebar width.
            const sWidth = getSidebarWidth();
            // content.style.marginLeft = sWidth + 'px';

            // Position the toggle button near the right edge of the sidebar.
            toggleBtn.style.left = (sWidth > 0 ? sWidth : 5) + 'px';

            // Set the toggle arrow icon.
            toggleIcon.className = (states[currentStateIndex] === 'hidden') ?
                'fas fa-angle-right' :
                'fas fa-angle-left';

            // 🔽 NEW: Hide all expanded submenus if sidebar is not in 'full' state
            if (states[currentStateIndex] !== 'full') {
                document.querySelectorAll('.submenu.active').forEach(el => {
                    el.classList.remove('active');
                });
            }

            if (states[currentStateIndex] !== 'icons') {
                document.querySelectorAll('.submenu-popup').forEach(el => {
                    el.style.display = "none";
                });
            }

            // 🔽 NEW: Hide all expanded submenus if sidebar is not in 'full' state
            if (states[currentStateIndex] == 'icons') {
                document.querySelectorAll('.menu-item').forEach(el => {
                    el.style.marginLeft = '0px';
                    el.style.marginRight = '0px';
                });

                document.querySelectorAll('.menu-text').forEach(el => {
                    el.style.fontSize = '0.8em';
                });

                document.querySelectorAll('.group-title').forEach(el => {
                    el.style.display = 'none';
                });

                
            }


            // Reset styles for the 'full' state
            if (states[currentStateIndex] == 'full') {
                document.querySelectorAll('.menu-item').forEach(el => {
                    el.style.marginLeft = '0.8em';
                    el.style.marginRight = '0.8em';
                });

                document.querySelectorAll('.menu-text').forEach(el => {
                    el.style.fontSize = '1em';
                });

                document.querySelectorAll('.group-title').forEach(el => {
                    el.style.display = 'block';
                });
            }

            document.querySelectorAll('.menu-item-header').forEach(el => {
                el.style.margin = '0px';
            });


        }


        // Helper function to create an element.
        function createElem(tag, className, innerHTML) {
            const el = document.createElement(tag);
            if (className) el.className = className;
            if (innerHTML) el.innerHTML = innerHTML;
            return el;
        }

        // Render sidebar menu items.
        function renderMenu() {
            const currentPath = window.location.pathname.replace(/^\/+/, ''); // e.g., 'dashboard'

            sidebar.innerHTML = '';

            sidebarMenuData.forEach((item, index) => {
                const prepend = item.prepend || '';
                const append = item.append || '';
                const itemType = item.itemType || '';
                const cssClasses = item.cssClasses || '';
                const iconCssClasses = item.iconCssClasses || '';
                const isActiveMenu = item.url === currentPath;

                const menuDiv = document.createElement('div');
                menuDiv.dataset.index = index;

                let content = "";
                if (itemType == "header") {
                    content = `<i class="${item.icon} "></i><span class="menu-text menu-header-text">${item.title}</span>`;
                    menuDiv.className = `menu-item ${isActiveMenu ? 'active' : ''} ${cssClasses}`.trim();

                } else if (itemType == "item-separator") {
                    content = `${item.title}`;
                } else {
                    content = `<i class="${item.icon} sidebar-icon"></i><span class="menu-text">${item.title}</span>`;
                    menuDiv.className = `menu-item ${isActiveMenu ? 'active' : ''} ${cssClasses}`.trim();
                }
                menuDiv.innerHTML = `${prepend}${content}${append}`;

                
                    
                // If the menu item has a submenu
                if (item.submenu && Array.isArray(item.submenu)) {
                    const submenuDiv = createElem('div', 'submenu');
                    let submenuHasActive = false;

                    item.submenu.forEach(sub => {
                        const subIsActive = sub.url === currentPath;
                        const link = createElem('a', subIsActive ? 'active' : '', sub.title);
                        link.href = `/${sub.url}`;

                        if (subIsActive) {
                            submenuHasActive = true;
                        }

                        link.addEventListener('click', () => {
                            if (states[currentStateIndex] === 'icons' && activePopup) {
                                activePopup.remove();
                                activePopup = null;
                            }
                        });

                        submenuDiv.appendChild(link);
                    });

                    if (submenuHasActive) {
                        menuDiv.classList.add('active');
                        submenuDiv.classList.add('active');
                    }

                    menuDiv.appendChild(submenuDiv);
                } else {
                    // No submenu, make the whole div a link
                    if (item.url !="#") {
                        menuDiv.style.cursor = 'pointer';
                        menuDiv.addEventListener('click', () => {
                            window.location.href = `/${item.url}`;
                        });
                    }
                }

                sidebar.appendChild(menuDiv);

                // Submenu toggle for states
                if (item.submenu) {
                    menuDiv.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (states[currentStateIndex] === 'full') {
                            const submenu = menuDiv.querySelector('.submenu');
                            submenu.classList.toggle('active');
                        } else if (states[currentStateIndex] === 'icons') {
                            if (activePopup) {
                                activePopup.remove();
                                activePopup = null;
                            }

                            activePopup = createElem('div', 'submenu-popup');
                            item.submenu.forEach(sub => {
                                const subIsActive = sub.url === currentPath;
                                const link = createElem('a', subIsActive ? 'active' : '', sub
                                .title);
                                link.href = `/${sub.url}`;

                                link.addEventListener('click', () => {
                                    if (activePopup) {
                                        activePopup.remove();
                                        activePopup = null;
                                    }
                                });

                                activePopup.appendChild(link);
                            });

                            const rect = menuDiv.getBoundingClientRect();
                            activePopup.style.top = rect.top + 'px';
                            activePopup.style.left = rect.right + 'px';
                            document.body.appendChild(activePopup);

                            document.addEventListener('click', function closePopup(e) {
                                if (activePopup && !activePopup.contains(e.target) && e.target !==
                                    menuDiv) {
                                    activePopup.remove();
                                    activePopup = null;
                                    document.removeEventListener('click', closePopup);
                                }
                            });
                        }








                    });
                }
            });
        }



        let activePopup = null;

        // Toggle the sidebar state on click.
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            currentStateIndex = (currentStateIndex + 1) % states.length;
            updateUI();
        });

        // Initialization routine.
        function initialize() {
            setMode();
            updateUI();
            // renderMenu will now be called when the Livewire event is received
        }

        // Listen for the Livewire event that provides the sidebar data
        document.addEventListener('livewire:initialized', function() {
            Livewire.dispatch('initializeMenuEvent'); // To show modal

            Livewire.on('sidebar-data-loaded', (event) => {
                // This event is triggered when the sidebar data is loaded
                //alert(JSON.stringify(event[0]));
                sidebarMenuData = event[0]; // Assign the data to the global variable

                renderMenu(); // Render the menu once the data is received
            });
        });



        window.addEventListener('resize', initialize);
        initialize();
    </script>
@endscript
