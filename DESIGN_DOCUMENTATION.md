# Documentation Design - Mini LMS Premium

## Vue d'ensemble
Ce document décrit l'architecture et le design premium implémenté pour les pages Mes Cours, Modules et Ressources du Mini-LMS.

## Thème Global

### Palette de Couleurs
- **Primary Blue**: `#1e3a8a` - Utilisé pour les éléments principaux
- **Secondary Blue**: `#3b82f6` - Boutons et accents
- **Background**: `#f8faff` - Fond premium
- **Text Dark**: `#1e293b` - Texte principal
- **Text Muted**: `#64748b` - Texte secondaire

### Typographie
- **Police**: Inter (font-weight: 600 pour les titres)
- **Titres H1**: 2.5rem, font-weight: 600
- **Titres H3**: 1.25-1.35rem, font-weight: 600
- **Corps**: 0.9rem

### Bordures et Arrondis
- **Cartes**: border-radius: 20-24px
- **Boutons**: border-radius: 30px (forme pill)
- **Icônes**: border-radius: 14-16px

## Page Mes Cours

### Fonctionnalités
1. **Système d'icônes intelligentes** - Détection automatique basée sur le titre du cours
   - Mathématiques → `fa-calculator`
   - Programmation → `fa-code`
   - Design → `fa-palette`
   - Data Science → `fa-chart-line`
   - Marketing → `fa-bullhorn`
   - Sciences → `fa-flask`
   - Langues → `fa-language`
   - Histoire-Géo → `fa-globe`
   - Général → `fa-book`

2. **Badges dynamiques** avec 9 catégories colorées
3. **Statistiques visuelles** - Modules et étudiants
4. **Barre de progression** avec gradient
5. **Recherche en temps réel** avec JavaScript
6. **État vide élégant** quand aucun cours n'existe

### Structure CSS
- Grille responsive: `grid-template-columns: repeat(auto-fill, minmax(340px, 1fr))`
- Effet hover avec translation verticale (-8px)
- Barre de progression animée en haut de carte
- Icônes avec rotation au hover

## Page Modules

### Fonctionnalités
1. **Liste verticale** au lieu de grille
2. **Badge d'ordre** (Module 1, Module 2, etc.)
3. **Icône de module** avec effet de rotation au hover
4. **Métadonnées** - Nombre de ressources et date de création
5. **Lien vers le cours parent**
6. **Recherche en temps réel**

### Structure CSS
- Layout horizontal avec flexbox
- Barre latérale gauche animée au hover
- Translation horizontale au hover (+8px)
- Badges avec dégradé bleu

## Page Ressources

### Fonctionnalités
1. **Système d'icônes intelligentes** basé sur le type de ressource
   - PDF/Document → `fa-file-pdf` (rouge)
   - Vidéo → `fa-video` (violet)
   - Image → `fa-image` (orange)
   - Lien → `fa-link` (bleu)
   - Audio → `fa-headphones` (rose)
   - Texte → `fa-file-alt` (vert)
   - Code → `fa-code` (vert foncé)
   - Général → `fa-file` (gris)

2. **Badges de type** colorés selon la catégorie
3. **Grille de cartes** responsive
4. **Boutons d'action circulaires**
5. **Recherche en temps réel**

### Structure CSS
- Grille responsive: `grid-template-columns: repeat(auto-fill, minmax(320px, 1fr))`
- Icônes avec dégradés de couleur selon le type
- Boutons circulaires avec effet scale au hover
- Footer avec bordure supérieure

## Architecture CSS Modulaire

### Fichiers CSS
```
assets/styles/
├── app.css          # Point d'entrée principal
├── global.css       # Variables et resets
├── sidebar.css      # Menu latéral
├── layout.css       # Structure de grille
├── login.css        # Page d'authentification
├── dashboard.css    # Tableau de bord
├── courses.css      # Page Mes Cours
├── modules.css      # Page Modules
└── resources.css    # Page Ressources
```

### Ordre d'import dans app.css
1. global.css
2. login.css
3. sidebar.css
4. layout.css
5. dashboard.css
6. courses.css
7. modules.css
8. resources.css

## Animations et Transitions

### Effets Hover
- **Cartes**: `transform: translateY(-8px)` avec shadow augmentée
- **Boutons**: `transform: translateY(-2px)` avec shadow renforcée
- **Icônes**: `transform: scale(1.1) rotate(5deg)`
- **Barre de progression**: Animation de la largeur

### Durées
- Transitions standard: `0.3s ease`
- Boutons: `0.2s ease`
- Barre de progression: `0.6s ease`

## Composants Réutilisables

### Boutons Principaux
```css
.btn-create-course
.btn-create-module
.btn-create-resource
```
- Style: Pill (border-radius: 30px)
- Couleur: var(--secondary-blue)
- Hover: var(--primary-blue) avec translation

### Champs de Recherche
```css
.course-search-wrapper
.module-search-wrapper
.resource-search-wrapper
```
- Icône FontAwesome à gauche
- Border-radius: 30px
- Focus: Border bleue avec shadow

### États Vides
```css
.empty-state
```
- Icône circulaire avec dégradé
- Texte centré
- Bouton d'action

## Responsive Design

### Breakpoints
- **Desktop**: > 1200px - Grille complète
- **Tablet**: 768px - 1200px - Grille adaptée
- **Mobile**: < 768px - Sidebar cachée, grille simple colonne

## JavaScript

### Fonctionnalités de Recherche
Chaque page (Cours, Modules, Ressources) dispose d'une recherche en temps réel qui filtre les cartes selon le titre.

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('courseSearch');
    searchInput.addEventListener('input', function(e) {
        // Filtrage des cartes
    });
});
```

## Prochaines Étapes Suggérées

1. **Formulaires CRUD** - Styliser les formulaires d'ajout/édition
2. **Page Profil** - Créer une page profil élégante
3. **Animations avancées** - Ajouter des micro-interactions
4. **Dark Mode** - Implémenter un thème sombre
5. **Accessibilité** - Améliorer l'accessibilité (ARIA labels)

## Règles de Design à Respecter

✅ Utiliser font-weight: 600 (jamais plus)
✅ Border-radius généreux (20-30px)
✅ Dégradés subtils pour les backgrounds
✅ Icônes FontAwesome 6.4.0
✅ Transitions fluides (0.3s ease)
✅ Shadows légères et élégantes
✅ Pas d'emojis dans les titres
✅ Interface épurée et premium
