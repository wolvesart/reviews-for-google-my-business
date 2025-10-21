<?php
/**
 * Google My Business Reviews - Template de la page admin
 *
 * Variables disponibles :
 * - $has_token
 * - $available_locations
 * - $current_account_id
 * - $current_location_id
 * - $has_credentials
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gmb-admin-wrap">
<?php include_once(WOLVES_GMB_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <div class="gmb-admin-container">
        <!-- Sidebar avec tabs -->
        <div class="gmb-admin-sidebar">
            <nav class="gmb-tabs-nav">

                <button class="gmb-tab-button active" data-tab="configuration">
                    <span class="dashicons dashicons-admin-generic"></span>
                    Configuration initiale
                </button>
                <button class="gmb-tab-button" data-tab="usage">
                    <span class="dashicons dashicons-editor-code"></span>
                    Utilisation
                </button>
                <button class="gmb-tab-button" data-tab="customization">
                    <span class="dashicons dashicons-admin-customizer"></span>
                    Personnalisation
                </button>
                <button class="gmb-tab-button" data-tab="help">
                    <span class="dashicons dashicons-admin-page"></span>
                    Documentation
                </button>
            </nav>
        </div>

        <!-- Contenu principal -->
        <div class="gmb-admin-content">
            <?php if (isset($_GET['status'])): ?>
                <?php
                $status = sanitize_text_field(wp_unslash($_GET['status']));
                if ($status === 'success'):
                    ?>
                    <div class="notice notice-success">
                        <p>Authentification réussie !</p>
                        <?php if (isset($_GET['auto_fetch']) && !empty($available_locations)): ?>
                            <p>Les comptes et locations ont été récupérés automatiquement. Veuillez sélectionner votre
                                location ci-dessous.</p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($status === 'revoked'): ?>
                    <div class="notice notice-success"><p>Accès révoqué avec succès !</p></div>
                <?php elseif ($status === 'location_saved'): ?>
                    <div class="notice notice-success"><p>Location configurée avec succès !</p></div>
                <?php elseif ($status === 'credentials_saved'): ?>
                    <div class="notice notice-success"><p>Identifiants API sauvegardés avec succès !</p></div>
                <?php elseif ($status === 'customization_saved'): ?>
                    <div class="notice notice-success"><p>Personnalisation enregistrée avec succès !</p></div>
                <?php else: ?>
                    <div class="notice notice-error"><p>Erreur d'authentification</p></div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Tab: Configuration -->
            <div class="gmb-tab-content active" data-tab-content="configuration">
                <div class="card">
                    <h2>1. Configuration de l'API Google</h2>

                    <?php if ($has_credentials): ?>
                        <div class="gmb_notice success">
                            <p>Identifiants API configurés</p>
                        </div>
                    <?php else: ?>
                        <div class="gmb_notice warning">
                            <strong>Identifiants API non configurés</strong><br>
                            Vous devez d'abord configurer vos identifiants Google Cloud pour utiliser cette
                            fonctionnalité.
                        </div>
                    <?php endif; ?>

                    <details <?php echo !$has_credentials ? 'open' : ''; ?>>
                        <summary style="cursor: pointer; font-weight: bold; padding: 10px 0;">
                            <?php echo $has_credentials ? 'Modifier les identifiants API' : 'Ajouter les identifiants API'; ?>
                        </summary>

                        <form method="post" class="gmb_form"
                              action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('gmb_save_credentials', 'gmb_credentials_nonce'); ?>
                            <input type="hidden" name="action" value="gmb_save_credentials">

                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="gmb_client_id">Client ID</label>
                                    </th>
                                    <td>
                                        <input type="text"
                                               name="gmb_client_id"
                                               id="gmb_client_id"
                                               class="regular-text"
                                               value="<?php echo esc_attr(GMB_CLIENT_ID); ?>"
                                               placeholder="123456789.apps.googleusercontent.com"
                                               required>
                                        <p class="description">
                                            Votre Client ID Google Cloud doit se terminer par <code>.apps.googleusercontent.com</code>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="gmb_client_secret">Client Secret</label>
                                    </th>
                                    <td>
                                        <input type="password"
                                               name="gmb_client_secret"
                                               id="gmb_client_secret"
                                               class="regular-text"
                                               value="<?php echo esc_attr(GMB_CLIENT_SECRET); ?>"
                                               placeholder="GOCSPX-xxxxxxxxxxxx"
                                               required>
                                        <p class="description">
                                            Votre Client Secret Google Cloud
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="gmb_redirect_uri">Redirect URI</label>
                                    </th>
                                    <td>
                                        <input type="url"
                                               name="gmb_redirect_uri"
                                               id="gmb_redirect_uri"
                                               class="regular-text"
                                               value="<?php echo esc_attr(GMB_REDIRECT_URI); ?>"
                                               placeholder="<?php echo esc_attr(admin_url('admin.php?page=gmb-settings&gmb_auth=1')); ?>"
                                               required>
                                        <p class="description">
                                            Par défaut :
                                            <code><?php echo esc_html(admin_url('admin.php?page=gmb-settings&gmb_auth=1')); ?></code><br>
                                            ⚠️ Cette URI doit correspondre à celle configurée dans Google Cloud
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p class="submit">
                                <button type="submit" class="button button-primary">
                                    Enregistrer les identifiants
                                </button>
                            </p>
                        </form>
                    </details>
                </div>

                <div class="card">
                    <h2>2. Authentification OAuth Google My Business</h2>

                    <?php if (!$has_credentials): ?>
                        <div class="gmb_notice warning">
                            <strong>Configurez d'abord vos identifiants API ci-dessus avant de vous
                                authentifier.</strong>
                        </div>
                    <?php elseif ($has_token): ?>
                        <div class="gmb_notice success"><p>Authentifié</p></div>
                        <p>
                            <a href="<?php echo esc_url(gmb_get_auth_url()); ?>" class="button">
                                Ré-authentifier
                            </a>
                            <button type="button" class="button"
                                    onclick="if(confirm('Supprimer les tokens ?')) location.href='<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=gmb_revoke'), 'gmb_revoke_action')); ?>'">
                                ⨉ Révoquer l'accès
                            </button>
                        </p>
                    <?php else: ?>
                        <div class="gmb_notice warning"><p>Non authentifié</p></div>
                        <p>
                            <a href="<?php echo esc_url(gmb_get_auth_url()); ?>" class="button button-primary">
                                Connecter avec Google
                            </a>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($has_token && !empty($available_locations)): ?>
                    <div class="card">
                        <h2>3. Sélectionner votre location</h2>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('gmb_save_location', 'gmb_location_nonce'); ?>
                            <input type="hidden" name="action" value="gmb_save_location">

                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="gmb_location_select">Location Google My Business</label>
                                    </th>
                                    <td>
                                        <select name="gmb_location" id="gmb_location_select" class="regular-text"
                                                required>
                                            <option value="">-- Sélectionnez une location --</option>
                                            <?php foreach ($available_locations as $location): ?>
                                                <?php
                                                $value = $location['account_id'] . '|' . $location['location_id'];
                                                $selected = ($location['account_id'] === $current_account_id && $location['location_id'] === $current_location_id) ? 'selected' : '';
                                                ?>
                                                <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>>
                                                    <?php echo esc_html($location['location_title']); ?>
                                                    (<?php echo esc_html($location['account_name']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description">
                                            Sélectionnez l'établissement Google My Business dont vous souhaitez afficher
                                            les avis.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p class="submit">
                                <button type="submit" class="button button-primary">
                                    Enregistrer la location
                                </button>
                                <button type="button" class="button" onclick="refreshLocations()">
                                    Rafraîchir la liste
                                </button>
                            </p>
                        </form>
                    </div>
                <?php elseif ($has_token && empty($available_locations)): ?>
                    <div class="card">
                        <h2>Récupérer vos locations</h2>
                        <p>Aucune location n'a été trouvée. Cliquez sur le bouton ci-dessous pour rechercher vos
                            établissements Google My Business.</p>
                        <p>
                            <button type="button" class="button button-primary" onclick="refreshLocations()">
                                Rechercher mes locations
                            </button>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <h2>Location non disponible</h2>
                        <p class="gmb_notice warning">
                            <strong>Vous devez d'abord vous authentifier</strong><br>
                            Allez dans l'onglet "Authentification" pour connecter votre compte Google.
                        </p>
                    </div>
                <?php endif; ?>


                <div class="card">
                    <h2>4. Tester la connexion</h2>
                    <p>
                        <button type="button" class="button button-primary" onclick="testGMBConnection()">
                            Tester la récupération des avis
                        </button>
                        <button type="button" class="button" onclick="clearGMBCache()">
                            Vider le cache
                        </button>
                    </p>
                    <div id="gmb-test-result"></div>
                </div>
            </div>

            <!-- Tab: Utilisation -->
            <div class="gmb-tab-content" data-tab-content="usage">
                <div class="card">
                    <h2>Utilisation du shortcode</h2>
                    <p>Pour afficher les avis Google My Business sur votre site, utilisez le shortcode suivant dans vos
                        pages, articles ou widgets :</p>

                    <div style="background: #f0f0f1; padding: 15px; border-radius: 4px; margin: 15px 0;">
                        <code style="font-size: 14px; color: #d63638;">[gmb_reviews limit="10"]</code>
                    </div>

                    <h3>Paramètres disponibles</h3>
                    <table class="form-table">
                        <tr>
                            <th><code>limit</code></th>
                            <td>Nombre d'avis à afficher (défaut: 50)</td>
                        </tr>
                        <tr>
                            <th><code>category</code></th>
                            <td>Filtrer par catégorie (utiliser le slug de la catégorie)</td>
                        </tr>
                        <tr>
                            <th><code>type (prochainement)</code></th>
                            <td>Slider ou grille Mansory</td>
                        </tr>
                    </table>

                    <h3>Exemples d'utilisation de base</h3>
                    <ul style="list-style: none; padding: 0; margin: 15px 0;">
                        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                            <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews]</code>
                            <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ Affiche tous les avis (maximum 50)</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                            <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                limit="5"]</code>
                            <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ Affiche 5 avis</span>
                        </li>
                        <li style="padding: 8px 0;">
                            <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                limit="20"]</code>
                            <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ Affiche 20 avis</span>
                        </li>
                    </ul>

                    <h3 style="margin-top: 30px;">Filtrage par catégorie</h3>
                    <p>Vous pouvez filtrer les avis par catégorie. Utilisez le <strong>slug</strong> de la catégorie
                        (visible dans la <a
                                href="<?php echo esc_url(admin_url('admin.php?page=gmb-manage-reviews')); ?>">liste des
                            avis</a>).</p>
                    <p><strong>Note :</strong> Si un avis a plusieurs catégories, il apparaîtra dans le filtrage de chacune de ces catégories.</p>

                    <ul style="list-style: none; padding: 0; margin: 15px 0;">
                        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                            <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                category="formation"]</code>
                            <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ Affiche uniquement les avis de la catégorie "formation"</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                            <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                category="formation" limit="5"]</code>
                            <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ Affiche 5 avis de la catégorie "formation"</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                            <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                category="coaching" limit="3"]</code>
                            <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ Affiche 3 avis de la catégorie "coaching"</span>
                        </li>
                        <li style="padding: 8px 0;">
                            <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                category=""]</code>
                            <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ Affiche uniquement les avis <strong>sans catégorie</strong></span>
                        </li>
                    </ul>

                    <div style="background: #DBEAFE; border-left: 4px solid #3772FF; padding: 12px 16px; margin: 20px 0; border-radius: 4px;">
                        <p style="margin: 0 0 10px 0;"><strong>💡 Astuces :</strong></p>
                        <ul style="margin: 0; padding-left: 20px;">
                            <li>Pour trouver le slug d'une catégorie, allez dans <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-manage-reviews')); ?>">Avis Google → Liste des avis</a> et consultez la section "Gestion des catégories".</li>
                            <li>Vous pouvez assigner plusieurs catégories à un même avis (cochez simplement plusieurs cases dans la colonne "Catégorie").</li>
                            <li>Un avis avec plusieurs catégories apparaîtra dans le filtrage de chacune de ces catégories.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tab: Personnalisation -->
            <div class="gmb-tab-content" data-tab-content="customization">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('gmb_save_customization', 'gmb_customization_nonce'); ?>
                    <input type="hidden" name="action" value="gmb_save_customization">

                    <div class="card">
                        <h3>Résumé</h3>
                        <p>Affiche la note moyenne et le nombre total d'avis</p>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="gmb_show_summary">Afficher le résumé des avis</label>
                                </th>
                                <td>
                                    <label class="gmb-toggle-switch">
                                        <input type="checkbox"
                                               name="gmb_show_summary"
                                               id="gmb_show_summary"
                                               value="1"
                                                <?php checked(get_option('gmb_show_summary', '1'), '1'); ?>>
                                        <span class="gmb-toggle-slider"></span>
                                    </label>
                                </td>
                            </tr>
                            <tr id="gmb_resume_text_color_row">
                                <th scope="row">
                                    <label for="gmb_resume_text_color">Couleur de la note et du texte</label>
                                </th>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="color"
                                               id="gmb_resume_text_color_picker"
                                               value="<?php echo esc_attr(get_option('gmb_resume_text_color', '#FFFFFF')); ?>"
                                               style="width: 50px; height: 35px; cursor: pointer;">
                                        <input type="text"
                                               id="gmb_resume_text_color_hex"
                                               value="<?php echo esc_attr(get_option('gmb_resume_text_color', '#FFFFFF')); ?>"
                                               pattern="^#[0-9A-Fa-f]{6}$"
                                               maxlength="7"
                                               placeholder="#000000"
                                               style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                        <input type="hidden"
                                               name="gmb_resume_text_color"
                                               id="gmb_resume_text_color"
                                               value="<?php echo esc_attr(get_option('gmb_resume_text_color', '#FFFFFF')); ?>">
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="card">

                        <h3>Carte d'avis</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="gmb_card_bg_color">Couleur de fond</label>
                                </th>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="color"
                                               id="gmb_card_bg_color_picker"
                                               value="<?php echo esc_attr(get_option('gmb_card_bg_color', '#17171A')); ?>"
                                               style="width: 50px; height: 35px; cursor: pointer;">
                                        <input type="text"
                                               id="gmb_card_bg_color_hex"
                                               value="<?php echo esc_attr(get_option('gmb_card_bg_color', '#17171A')); ?>"
                                               pattern="^#[0-9A-Fa-f]{6}$"
                                               maxlength="7"
                                               placeholder="#000000"
                                               style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                        <input type="hidden"
                                               name="gmb_card_bg_color"
                                               id="gmb_card_bg_color"
                                               value="<?php echo esc_attr(get_option('gmb_card_bg_color', '#17171A')); ?>">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="gmb_card_border_radius">Arrondi des bords</label>
                                </th>
                                <td>
                                    <input type="number"
                                           name="gmb_card_border_radius"
                                           id="gmb_card_border_radius"
                                           value="<?php echo esc_attr(get_option('gmb_card_border_radius', '32')); ?>"
                                           min="0"
                                           max="50"
                                           class="small-text"> px
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="gmb_star_color">Couleur des étoiles</label>
                                </th>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="color"
                                               id="gmb_star_color_picker"
                                               value="<?php echo esc_attr(get_option('gmb_star_color', '#F85430')); ?>"
                                               style="width: 50px; height: 35px; cursor: pointer;">
                                        <input type="text"
                                               id="gmb_star_color_hex"
                                               value="<?php echo esc_attr(get_option('gmb_star_color', '#F85430')); ?>"
                                               pattern="^#[0-9A-Fa-f]{6}$"
                                               maxlength="7"
                                               placeholder="#000000"
                                               style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                        <input type="hidden"
                                               name="gmb_star_color"
                                               id="gmb_star_color"
                                               value="<?php echo esc_attr(get_option('gmb_star_color', '#F85430')); ?>">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="gmb_text_color">Couleur du texte</label>
                                </th>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="color"
                                               id="gmb_text_color_picker"
                                               value="<?php echo esc_attr(get_option('gmb_text_color', '#AEAEAE')); ?>"
                                               style="width: 50px; height: 35px; cursor: pointer;">
                                        <input type="text"
                                               id="gmb_text_color_hex"
                                               value="<?php echo esc_attr(get_option('gmb_text_color', '#AEAEAE')); ?>"
                                               pattern="^#[0-9A-Fa-f]{6}$"
                                               maxlength="7"
                                               placeholder="#000000"
                                               style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                        <input type="hidden"
                                               name="gmb_text_color"
                                               id="gmb_text_color"
                                               value="<?php echo esc_attr(get_option('gmb_text_color', '#AEAEAE')); ?>">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="gmb_text_color">Couleur d'accent</label>
                                </th>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="color"
                                               id="gmb-accent-color_picker"
                                               value="<?php echo esc_attr(get_option('gmb-accent-color', '#FFFFFF')); ?>"
                                               style="width: 50px; height: 35px; cursor: pointer;">
                                        <input type="text"
                                               id="gmb-accent-color_hex"
                                               value="<?php echo esc_attr(get_option('gmb-accent-color', '#FFFFFF')); ?>"
                                               pattern="^#[0-9A-Fa-f]{6}$"
                                               maxlength="7"
                                               placeholder="#000000"
                                               style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                        <input type="hidden"
                                               name="gmb-accent-color"
                                               id="gmb-accent-color"
                                               value="<?php echo esc_attr(get_option('gmb-accent-color', '#FFFFFF')); ?>">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="gmb_text_color">Couleur du nom</label>
                                </th>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="color"
                                               id="gmb_text_color_name_picker"
                                               value="<?php echo esc_attr(get_option('gmb_text_color_name', '#FFFFFF')); ?>"
                                               style="width: 50px; height: 35px; cursor: pointer;">
                                        <input type="text"
                                               id="gmb_text_color_name_hex"
                                               value="<?php echo esc_attr(get_option('gmb_text_color_name', '#FFFFFF')); ?>"
                                               pattern="^#[0-9A-Fa-f]{6}$"
                                               maxlength="7"
                                               placeholder="#000000"
                                               style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                        <input type="hidden"
                                               name="gmb_text_color_name"
                                               id="gmb_text_color_name"
                                               value="<?php echo esc_attr(get_option('gmb_text_color_name', '#FFFFFF')); ?>">
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary">
                                Enregistrer la personnalisation
                            </button>
                            <button type="button" class="button" onclick="resetGMBCustomization()">
                                Réinitialiser les valeurs par défaut
                            </button>
                        </p>
                </form>
            </div>
        </div>

        <!-- Tab: Documentation -->
        <div class="gmb-tab-content" data-tab-content="help">
            <div class="card">
                <h2>Configuration de l'API Google</h2>

                <h4 style="margin: 15px 0 10px 0;">1. Créer le projet</h4>
                <ol style="margin: 0 0 0 20px;">
                    <li>Allez sur <a href="https://console.cloud.google.com/" target="_blank">Google Cloud
                            Console</a></li>
                    <li>Créez un nouveau projet ou sélectionnez-en un existant</li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;">2. Activer les APIs requises</h4>
                <ol style="margin: 0 0 0 20px;">
                    <li>Dans le menu, allez dans "APIs et services" → "Bibliothèque"</li>
                    <li>Recherchez et activez : <strong>"Google My Business API"</strong></li>
                    <li>Recherchez et activez : <strong>"My Business Account Management API"</strong></li>
                    <li>Recherchez et activez : <strong>"My Business Business Information API"</strong></li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;">3. Configurer l'écran de consentement OAuth</h4>
                <ol style="margin: 0 0 0 20px;">
                    <li>Allez dans "APIs et services" → "Écran de consentement OAuth"</li>
                    <li>Choisissez <strong>"Externe"</strong> comme type d'utilisateur</li>
                    <li>Remplissez les informations obligatoires :
                        <ul style="margin: 5px 0 0 20px;">
                            <li>Nom de l'application</li>
                            <li>Adresse e-mail de l'assistance utilisateur</li>
                            <li>Adresse e-mail du développeur</li>
                        </ul>
                    </li>
                    <li><strong>IMPORTANT</strong> : Dans "Scopes", ajoutez ces scopes OAuth :
                        <ul style="margin: 5px 0 0 20px;">
                            <li><code>https://www.googleapis.com/auth/business.manage</code></li>
                            <li><code>https://www.googleapis.com/auth/plus.business.manage</code></li>
                        </ul>
                    </li>
                    <li>Dans "Utilisateurs de test", ajoutez votre adresse Gmail</li>
                    <li>Sauvegardez</li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;">4. Créer les identifiants OAuth 2.0</h4>
                <ol style="margin: 0 0 0 20px;">
                    <li>Allez dans "APIs et services" → "Identifiants"</li>
                    <li>Cliquez sur "+ CRÉER DES IDENTIFIANTS" → "ID client OAuth"</li>
                    <li>Type d'application : <strong>"Application Web"</strong></li>
                    <li>Nom : donnez un nom (ex: "WordPress GMB Reviews")</li>
                    <li><strong>URI de redirection autorisées</strong> : Ajoutez EXACTEMENT l'URI ci-dessus</li>
                    <li>Cliquez sur "Créer"</li>
                    <li>Copiez le Client ID et Client Secret dans les champs ci-dessus</li>
                </ol>
            </div>
        </div>

    </div><!-- .gmb-admin-content -->
</div><!-- .gmb-admin-container -->
</div><!-- .wrap -->

<?php require WOLVES_GMB_PLUGIN_DIR . 'templates/admin-scripts.php'; ?>
