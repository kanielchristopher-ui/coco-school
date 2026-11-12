<?php
/**
 * Illustration vectorielle de l'école (aucune image externe).
 * Couleurs pilotées par le CSS : --art-1 (principal), --art-2 (clair), --art-3 (accent), --art-4 (sombre).
 * Utilisation : <?php include __DIR__ . '/partials/school_art.php'; ?>
 */
?>
<svg class="school-art" viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="<?= e(t('Illustration of the school campus building')) ?>">
    <ellipse cx="200" cy="288" rx="170" ry="16" fill="var(--art-2)" opacity=".7"/>
    <circle cx="318" cy="64" r="30" fill="var(--art-3)"/>
    <circle cx="318" cy="64" r="44" fill="var(--art-3)" opacity=".25"/>
    <!-- nuages -->
    <g fill="#fff" opacity=".9"><ellipse cx="86" cy="62" rx="34" ry="12"/><ellipse cx="108" cy="54" rx="24" ry="12"/><ellipse cx="250" cy="40" rx="26" ry="9"/></g>
    <!-- bâtiment -->
    <rect x="96" y="132" width="208" height="150" rx="8" fill="#fff"/>
    <rect x="96" y="132" width="208" height="150" rx="8" fill="var(--art-2)" opacity=".45"/>
    <polygon points="82,138 200,76 318,138" fill="var(--art-1)"/>
    <rect x="170" y="150" width="60" height="22" rx="6" fill="#fff"/>
    <circle cx="200" cy="161" r="7" fill="var(--art-3)"/>
    <rect x="170" y="204" width="60" height="78" rx="8" fill="var(--art-4)"/>
    <circle cx="219" cy="246" r="3" fill="var(--art-3)"/>
    <g fill="var(--art-1)"><rect x="116" y="170" width="36" height="34" rx="6"/><rect x="248" y="170" width="36" height="34" rx="6"/><rect x="116" y="224" width="36" height="34" rx="6"/><rect x="248" y="224" width="36" height="34" rx="6"/></g>
    <g stroke="#fff" stroke-width="2" opacity=".7"><path d="M134 170v34M116 187h36M266 170v34M248 187h36M134 224v34M116 241h36M266 224v34M248 241h36"/></g>
    <!-- drapeau -->
    <line x1="200" y1="76" x2="200" y2="40" stroke="var(--art-4)" stroke-width="3"/>
    <path d="M200 40h30l-8 8 8 8h-30z" fill="var(--art-3)"/>
    <!-- arbres -->
    <g><rect x="52" y="226" width="8" height="56" rx="3" fill="var(--art-4)"/><circle cx="56" cy="214" r="30" fill="var(--art-1)"/><circle cx="40" cy="228" r="18" fill="var(--art-1)" opacity=".8"/></g>
    <g><rect x="342" y="240" width="7" height="42" rx="3" fill="var(--art-4)"/><circle cx="346" cy="230" r="22" fill="var(--art-1)"/></g>
    <!-- livres -->
    <g><rect x="120" y="272" width="62" height="12" rx="3" fill="var(--art-3)"/><rect x="126" y="262" width="52" height="11" rx="3" fill="#fff"/><rect x="122" y="252" width="58" height="11" rx="3" fill="var(--art-4)"/></g>
    <!-- élèves -->
    <g><circle cx="226" cy="262" r="9" fill="#f4c7a1"/><rect x="217" y="271" width="18" height="22" rx="7" fill="var(--art-3)"/><path d="M217 258q9-12 18 0z" fill="var(--art-4)"/></g>
    <g><circle cx="256" cy="266" r="8" fill="#b9805a"/><rect x="248" y="274" width="16" height="19" rx="6" fill="#fff"/><path d="M248 263q8-11 16 0z" fill="var(--art-4)"/></g>
    <g><circle cx="286" cy="268" r="7" fill="#8a5a3c"/><rect x="279" y="275" width="14" height="18" rx="6" fill="var(--art-1)"/><path d="M279 266q7-10 14 0z" fill="#222"/></g>
</svg>
