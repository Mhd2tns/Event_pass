console.log('JS menu.js version VITRINE chargé');

// Données du menu
console.log('Script best-sellers chargé');

const menuData = {
    obuns: [
        {
            name: "Le Must",
            description: "2 steaks 45G et de CORDON BLEU parfumés de CHEDDARS. Frites et boisson incluses.",
            price: "11,50€",
            image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1998&q=80",
            badge: "Populaire"
        },
        {
            name: "Le Grec à la broche",
            description: "VIANDE KEBAB coupée à la BROCHE dans le pain de votre choix ! Frites et boisson incluses.",
            price: "11,50€",
            image: "https://images.unsplash.com/photo-1550547660-d9450f859349?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1965&q=80",
            badge: "Nouveau"
        },
        {
            name: "Croque Spéciale",
            description: "CREME FRAICHE, JAMBON, 2 STEAKS, OEUF et FROMAGES maison. Frites et boisson incluses.",
            price: "9,50€",
            image: "https://images.unsplash.com/photo-1606755962773-d324e0a13086?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80",
            badge: "Best-seller"
        }
    ],
    omix: [
        {
            name: "Le Mix",
            description: "VIANDE KEBAB coupée à la BROCHE, 2 STEAKS, CHEDDARS et SAUCE MAISON. Frites et boisson incluses.",
            price: "12,50€",
            image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1998&q=80",
            badge: "Populaire"
        }
    ],
    oburger: [
        {
            name: "Le Burger",
            description: "2 STEAKS, CHEDDARS, SALADE, TOMATE, OIGNON et SAUCE MAISON. Frites et boisson incluses.",
            price: "10,50€",
            image: "https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1998&q=80",
            badge: "Best-seller"
        }
    ],
    boissons: [
        {
            name: "Coca-Cola",
            description: "33cl",
            price: "2,50€",
            image: "https://images.unsplash.com/photo-1554866585-cd94860890b7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1965&q=80"
        },
        {
            name: "Fanta",
            description: "33cl",
            price: "2,50€",
            image: "https://images.unsplash.com/photo-1624517452488-04869289c4ca?q=80&w=1006&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
        },
        {
            name: "Sprite",
            description: "33cl",
            price: "2,50€",
            image: "https://images.unsplash.com/photo-1680404005217-a441afdefe83?q=80&w=928&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
        },
        {
            name: "Eau minérale",
            description: "50cl",
            price: "1,50€",
            image: "https://images.unsplash.com/photo-1595994195534-d5219f02f99f?fm=jpg&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8bWluZXJhbCUyMHdhdGVyfGVufDB8fDB8fHww&ixlib=rb-4.1.0&q=60&w=3000"
        },
        {
            name: "Milkshake",
            description: "Chocolat, Vanille, Fraise",
            price: "4,50€",
            image: "https://images.unsplash.com/photo-1648999599232-fd999a6b1d91?q=80&w=627&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
        }
    ],
    desserts: [
        {
            name: "Tiramisu",
            description: "Maison",
            price: "5,50€",
            image: "https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80"
        },
        {
            name: "Mousse au chocolat",
            description: "Maison",
            price: "5,50€",
            image: "https://images.unsplash.com/photo-1603032305813-be7441bc1037?w=900&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8bW91c3NlJTIwYXUlMjBjaG9jb2xhdHxlbnwwfHwwfHx8MA%3D%3D"
        },
        {
            name: "Crème brûlée",
            description: "Maison",
            price: "5,50€",
            image: "https://images.unsplash.com/photo-1676300184943-09b2a08319a3?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
        },
        {
            name: "Glace artisanale",
            description: "2 boules",
            price: "4,50€",
            image: "https://images.unsplash.com/photo-1567206563064-6f60f40a2b57?q=80&w=1548&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
        }
    ]
};

// Fonction pour créer un élément de menu
function createMenuItem(item) {
    const menuItem = document.createElement('div');
    menuItem.className = 'bg-white rounded-xl shadow-lg overflow-hidden opacity-0 translate-y-8 transition-all duration-700 menu-item';
    setTimeout(() => {
        menuItem.classList.remove('opacity-0', 'translate-y-8');
        menuItem.classList.add('opacity-100', 'translate-y-0');
    }, 100);
    let badgeHtml = '';
    if (item.badge) {
        badgeHtml = `
            <div class="absolute top-4 right-4 bg-orange-600 text-white px-3 py-1 rounded-full font-bold">${item.badge}</div>
        `;
    }
    menuItem.innerHTML = `
        <div class="relative h-48 overflow-hidden group">
            <img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 menu-img">
            ${badgeHtml}
        </div>
        <div class="p-6">
            <h3 class="text-xl font-bold mb-2">${item.name}</h3>
            <p class="text-gray-600 mb-4">${item.description}</p>
            <div class="flex justify-end items-center">
                <span class="text-orange-600 font-bold text-lg">${item.price}</span>
            </div>
        </div>
    `;
    return menuItem;
}

// Fonction pour initialiser le menu
function initializeMenu() {
    const menuContainer = document.getElementById('menu-container');
    if (!menuContainer) return;
    menuContainer.innerHTML = '';

    // Créer les sections pour chaque catégorie avec un id unique
    Object.entries(menuData).forEach(([category, items]) => {
        const section = document.createElement('section');
        section.className = 'mb-16';
        section.id = `section-${category}`;

        // Génération du titre avec DA du logo O'Buns (robuste)
        const title = document.createElement('h2');
        title.className = 'text-3xl font-bold mb-8 flex flex-col items-center';
        let displayName = category;
        if (category.toLowerCase().startsWith('obuns')) displayName = "O'Buns";
        if (category.toLowerCase().startsWith('omix')) displayName = "O'Mix";
        if (category.toLowerCase().startsWith('oburger')) displayName = "O'Burger";
        // Mettre la première lettre en majuscule, le reste en minuscules (sauf O'...)
        if (!displayName.includes("'")) {
            displayName = displayName.charAt(0).toUpperCase() + displayName.slice(1).toLowerCase();
        }
        let titleHTML = '';
        if (displayName.includes("'")) {
            const parts = displayName.split("'");
            titleHTML = `<span class='text-orange-600' style='letter-spacing:normal;'>${parts[0]}'</span><span class='text-gray-800' style='letter-spacing:normal;'>${parts[1]}</span>`;
        } else {
            titleHTML = `<span class='text-orange-600' style='letter-spacing:normal;'>${displayName.charAt(0)}</span><span class='text-gray-800' style='letter-spacing:normal;'>${displayName.slice(1)}</span>`;
        }
        // Conteneur pour centrer le soulignement
        const titleText = document.createElement('span');
        titleText.className = 'relative inline-block';
        titleText.innerHTML = titleHTML;
        title.appendChild(titleText);
        // Soulignement orange sous le texte (largeur auto, centré)
        const underline = document.createElement('span');
        underline.style.position = 'absolute';
        underline.style.left = '50%';
        underline.style.transform = 'translateX(-50%)';
        underline.style.bottom = '-6px';
        underline.style.height = '3px';
        underline.style.width = '70%';
        underline.style.background = '#e67e22';
        underline.style.borderRadius = '2px';
        underline.style.display = 'block';
        underline.style.zIndex = '1';
        underline.style.opacity = '0.85';
        underline.style.pointerEvents = 'none';
        titleText.appendChild(underline);

        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-3 gap-8';

        items.forEach(item => {
            grid.appendChild(createMenuItem(item));
        });

        section.appendChild(title);
        section.appendChild(grid);
        menuContainer.appendChild(section);
    });

    // Ajout du scroll vers la section au clic sur les boutons de filtre
    const filterBtns = document.querySelectorAll('.category-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const category = btn.getAttribute('data-category');
            if (category === 'all') {
                window.scrollTo({
                    top: document.getElementById('menu-container').offsetTop - 80,
                    behavior: 'smooth'
                });
            } else {
                const section = document.getElementById(`section-${category}`);
                if (section) {
                    window.scrollTo({
                        top: section.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

// Initialiser le menu au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    initializeMenu();
}); 