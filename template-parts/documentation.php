<div class="title">
    <h2><?php esc_html_e('Google API Configuration Guide', 'reviews-for-google-my-business'); ?></h2>
    <p><?php esc_html_e('This process requires a Google Cloud Console account. The complete setup takes approximately 15 minutes. Step 4 requires validation from Google which can take 1-3 business days.', 'reviews-for-google-my-business'); ?></p>
</div>
<div class="card-list">
    <!-- STEP 1: Create Google Cloud Project -->
    <div class="card">
        <h3><?php esc_html_e('1. Create a Google Cloud Console Account and Project', 'reviews-for-google-my-business'); ?></h3>

        <h4><?php esc_html_e('Access Google Cloud Console', 'reviews-for-google-my-business'); ?></h4>
        <ol>
            <li>
                <?php
                /* translators: %s: URL to Google Cloud Console */
                printf(wp_kses_post(__('Go to <a href="%s" target="_blank">Google Cloud Console</a>', 'reviews-for-google-my-business')), esc_url('https://console.cloud.google.com/')); ?></li>
            <li><?php esc_html_e('Sign in with your Google account (Gmail).', 'reviews-for-google-my-business'); ?></li>
        </ol>

        <h4><?php esc_html_e('Create a New Project', 'reviews-for-google-my-business'); ?></h4>
        <ol>
            <li><?php esc_html_e('In the top navigation bar, click on the project dropdown (it may say "Select a project" if you\'re new).', 'reviews-for-google-my-business'); ?></li>
            <li><?php echo wp_kses_post(__('Click on <strong>"New Project"</strong> in the top right corner of the modal.', 'reviews-for-google-my-business')); ?></li>
            <li><?php esc_html_e('Enter a project name (example: "My Business Reviews" or "WordPress GMB API"). You can leave the organization field empty if you don\'t have one.', 'reviews-for-google-my-business'); ?></li>
            <li><?php echo wp_kses_post(__('Click <strong>"Create"</strong>.', 'reviews-for-google-my-business')); ?></li>
            <li><?php esc_html_e('Wait a few seconds for the project to be created, then select it from the project dropdown.', 'reviews-for-google-my-business'); ?></li>
        </ol>

        <div class="gmb-notice warning">
            <p>
                <strong><?php esc_html_e('Note:', 'reviews-for-google-my-business'); ?></strong> <?php esc_html_e('Make sure you have the correct project selected in the top navigation bar before proceeding to the next steps.', 'reviews-for-google-my-business'); ?>
            </p>
        </div>
    </div>

    <!-- STEP 2: Configure OAuth Consent Screen -->
    <div class="card">
        <h3><?php esc_html_e('2. Configure OAuth Consent Screen', 'reviews-for-google-my-business'); ?></h3>
        <p><?php esc_html_e('Before creating credentials, you must configure the OAuth consent screen. This is what users will see when authorizing your application to access their Google My Business data.', 'reviews-for-google-my-business'); ?></p>

        <h4><?php esc_html_e('Initial Configuration', 'reviews-for-google-my-business'); ?></h4>
        <ol>
            <li><?php echo wp_kses_post(__('In the left sidebar menu, go to <strong>"APIs & Services → OAuth consent screen"</strong>.', 'reviews-for-google-my-business')); ?></li>
            <li><?php echo wp_kses_post(__('You have a screen with "Google Auth Platform not configured yet" and a button <strong>"Get started"</strong>. Click the button.', 'reviews-for-google-my-business')); ?></li>
            <li>
                <?php echo wp_kses_post(__('Fill the project configuration and click on <strong>"Create"</strong>', 'reviews-for-google-my-business')); ?>
                <div class="config-table">
                    <table>
                        <tr>
                            <th><?php esc_html_e('App information', 'reviews-for-google-my-business'); ?></th>
                            <td>
                                <p><?php esc_html_e('App name : [Enter a name for your application (e.g., "My website reviews")].', 'reviews-for-google-my-business'); ?></p>
                                <p><?php esc_html_e('Support email : [Select your email address from the dropdown.]', 'reviews-for-google-my-business'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Audience', 'reviews-for-google-my-business'); ?></th>
                            <td>
                                <p><?php esc_html_e('Choose : "External" (if you have a Google Workspace account choose "Internal")', 'reviews-for-google-my-business'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </li>
        </ol>

        <h4><?php esc_html_e('Add Test Users (If you have Google Workspace account, skip this step)', 'reviews-for-google-my-business'); ?></h4>
        <ol>
            <li><?php echo wp_kses_post(__('In the left sidebar, go to <strong>"Google Auth Platform → Audience"</strong>.', 'reviews-for-google-my-business')); ?></li>
            <li><?php echo wp_kses_post(__('In the <strong>"Test users"</strong> section, click <strong>"+ Add test user"</strong>.', 'reviews-for-google-my-business')); ?></li>
            <li><?php esc_html_e('Add the Gmail address(es) that manage your Google My Business account.', 'reviews-for-google-my-business'); ?></li>
            <li><?php echo wp_kses_post(__('Click <strong>"Save"</strong>.', 'reviews-for-google-my-business')); ?></li>
        </ol>
    </div>

    <!-- STEP 3: Create OAuth 2.0 Credentials -->
    <div class="card">
        <h3><?php esc_html_e('3. Create OAuth 2.0 Client ID and Configure Scopes', 'reviews-for-google-my-business'); ?></h3>
        <p><?php esc_html_e('Now you\'ll create the Client ID and Client Secret that the plugin needs to connect to Google\'s APIs, and configure the necessary scopes.', 'reviews-for-google-my-business'); ?></p>

        <h4><?php esc_html_e('Start Creating OAuth Client', 'reviews-for-google-my-business'); ?></h4>
        <ul>
            <li><?php echo wp_kses_post(__('<strong>Option 1:</strong> Click the <strong>"Create OAuth client"</strong> button from the OAuth Consent Screen page.', 'reviews-for-google-my-business')); ?></li>
            <li><?php echo wp_kses_post(__('<strong>Option 2:</strong> Go to <strong>"APIs & Services" → "Credentials"</strong>, then click <strong>"+ CREATE CREDENTIALS" → "OAuth client ID"</strong>.', 'reviews-for-google-my-business')); ?></li>
        </ul>

        <h4><?php esc_html_e('Configure Client Details', 'reviews-for-google-my-business'); ?></h4>
        <ol>
            <li><?php echo wp_kses_post(__('Fill the OAuth client ID configuration and click on <strong>"Create"</strong>', 'reviews-for-google-my-business')); ?>
                <div class="config-table">
                    <table>
                        <tr>
                            <th><?php esc_html_e('Application type', 'reviews-for-google-my-business'); ?></th>
                            <td>
                                <p><?php echo wp_kses_post(__('Select <strong>"Web application"</strong>', 'reviews-for-google-my-business')); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Name', 'reviews-for-google-my-business'); ?></th>
                            <td>
                                <p><?php esc_html_e('Give it a descriptive name ("WordPress GMB Reviews Plugin")', 'reviews-for-google-my-business'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Authorized redirect URIs', 'reviews-for-google-my-business'); ?></th>
                            <td>
                                <p><?php echo wp_kses_post(__('Click <strong>"+ ADD URI"</strong>', 'reviews-for-google-my-business')); ?></p>
                                <p><?php echo wp_kses_post(__('Copy and paste the Redirect URI :', 'reviews-for-google-my-business')); ?><p>
                                    <code><?php echo esc_attr(admin_url('admin.php?page=gmb-settings&wgmbr_auth=1')); ?></code>
                            </td>
                        </tr>
                    </table>
                </div>
            </li>
            <li><?php echo wp_kses_post(__('A modal will appear with your <strong>Client ID</strong> and <strong>Client Secret</strong>.', 'reviews-for-google-my-business')); ?></li>
            <li><?php echo wp_kses_post(__('Copy your Client ID and client Secret and save in safe place, be careful you can copy Client Secret only once !', 'reviews-for-google-my-business')); ?></li>
            <li><?php echo wp_kses_post(__('Go back to the plugin and go to the <strong>"Configuration"</strong> page and paste your client ID/Secret into the specific fields', 'reviews-for-google-my-business')); ?></li>
        </ol>

        <div class="gmb-notice warning">
            <p>
                <?php esc_html_e('If you lose your customer secret code, you can create a new one on the "Add a secret code" configuration page. A new secret code will then be generated. If it is no longer in use, delete the old one.', 'reviews-for-google-my-business'); ?>
            </p>
        </div>


    </div>

    <!-- STEP 4: Request Access to Google My Business API -->
    <div class="card">
        <h3><?php esc_html_e('4. Request Access to Google My Business API', 'reviews-for-google-my-business'); ?></h3>

        <div class="gmb-notice warning">
            <p>
                <strong><?php esc_html_e('Important:', 'reviews-for-google-my-business'); ?></strong> <?php esc_html_e('The Google My Business API (also called Google Business Profile API) is restricted and requires explicit approval from Google. This is a mandatory step and can take 1-3 business days.', 'reviews-for-google-my-business'); ?>
            </p>
        </div>

        <h4><?php esc_html_e('Why is this step necessary?', 'reviews-for-google-my-business'); ?></h4>
        <p><?php esc_html_e('Google has restricted access to the Business Profile APIs to prevent abuse. Even though you\'ve created a project and credentials, you won\'t be able to access business reviews until Google approves your project.', 'reviews-for-google-my-business'); ?></p>

        <h4><?php esc_html_e('Submit the Access Request Form', 'reviews-for-google-my-business'); ?></h4>
        <ol>
            <li><?php
                /* translators: %s: URL to Google Business Profile API request form */
                printf(wp_kses_post(__('Go to the <a href="%s" target="_blank">Google Business Profile API Access Request Form</a>.', 'reviews-for-google-my-business')), esc_url('https://support.google.com/business/contact/api_default')); ?></li>
            <li><?php echo wp_kses_post(__('In the section "How can we help you?", select <strong>"Request for basic access to API"</strong>', 'reviews-for-google-my-business')); ?>
            <li><?php echo wp_kses_post(__('Fill rest of the request form and click on <strong>"Send"</strong>', 'reviews-for-google-my-business')); ?></li>
            <li><?php esc_html_e('Sign in with the same Google account you used to create the Cloud Console project.', 'reviews-for-google-my-business'); ?></li>
            <li><?php esc_html_e('Fill out the form with the following information:', 'reviews-for-google-my-business'); ?></li>
            <li><?php esc_html_e('Submit the form.', 'reviews-for-google-my-business'); ?></li>
        </ol>

        <h4><?php esc_html_e('Wait for Google\'s Approval', 'reviews-for-google-my-business'); ?></h4>
        <p><?php esc_html_e('Google will review your request. This typically takes 1-3 business days, but can sometimes take longer.', 'reviews-for-google-my-business'); ?></p>

        <h4><?php esc_html_e('Enable the Google Business Profile API', 'reviews-for-google-my-business'); ?></h4>
        <p><?php esc_html_e('Once you receive approval, you need to enable the API:', 'reviews-for-google-my-business'); ?></p>

        <ol>
            <li><?php echo wp_kses_post(__('Go back to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.', 'reviews-for-google-my-business')); ?></li>
            <li><?php esc_html_e('Make sure your project is selected in the top navigation bar.', 'reviews-for-google-my-business'); ?></li>
            <li><?php echo wp_kses_post(__('In the search bar at the top, type <strong>"Google Business Profile API"</strong>.', 'reviews-for-google-my-business')); ?></li>
            <li><?php echo wp_kses_post(__('Click on <strong>"Google Business Profile API"</strong> in the results.', 'reviews-for-google-my-business')); ?></li>
            <li><?php echo wp_kses_post(__('Click the <strong>"ENABLE"</strong> button.', 'reviews-for-google-my-business')); ?></li>
            <li><?php esc_html_e('Optionally, also enable these related APIs (follow the same process):', 'reviews-for-google-my-business'); ?>
                <ul>
                    <li><strong>My Business Account Management API</strong></li>
                    <li><strong>My Business Business Information API</strong></li>
                </ul>
            </li>
        </ol>

        <div class="gmb-notice success">
            <p>
                <strong><?php esc_html_e('Success!', 'reviews-for-google-my-business'); ?></strong> <?php esc_html_e('Once the APIs are enabled, you can return to this page and complete the authentication by clicking "Connect to Google My Business" above.', 'reviews-for-google-my-business'); ?>
            </p>
        </div>
    </div>

    <!-- Troubleshooting Section -->
    <div class="card">
        <h3><?php esc_html_e('Troubleshooting', 'reviews-for-google-my-business'); ?></h3>

        <details class="accordion-details">
            <summary>
                <?php esc_html_e('Error: "Access Not Configured"', 'reviews-for-google-my-business'); ?>
            </summary>
            <p><?php esc_html_e('This means you haven\'t been approved for the Google Business Profile API yet, or you haven\'t enabled it after approval. Complete Step 4 above.', 'reviews-for-google-my-business'); ?></p>
        </details>

        <details class="accordion-details">
            <summary>
                <?php esc_html_e('Error: "Invalid OAuth Redirect URI"', 'reviews-for-google-my-business'); ?>
            </summary>
            <p><?php esc_html_e('Make sure the Redirect URI in your Google Cloud Console credentials EXACTLY matches the one shown in the gray box above. Check for trailing slashes and extra spaces.', 'reviews-for-google-my-business'); ?></p>
        </details>

        <details class="accordion-details">
            <summary>
                <?php esc_html_e('Error: "Access blocked: This app\'s request is invalid"', 'reviews-for-google-my-business'); ?>
            </summary>
            <p><?php esc_html_e('This usually means you haven\'t added the correct OAuth scopes in Step 2. Go back and verify both scopes are added.', 'reviews-for-google-my-business'); ?></p>
        </details>

        <details class="accordion-details">
            <summary>
                <?php esc_html_e('Error: "This app is blocked"', 'reviews-for-google-my-business'); ?>
            </summary>
            <p><?php esc_html_e('If your app is in Testing mode, make sure the Google account you\'re using is added as a Test User in Step 2.', 'reviews-for-google-my-business'); ?></p>
        </details>

        <hr>

        <p>
            <?php esc_html_e('Need more help?', 'reviews-for-google-my-business'); ?>
            <a href="https://developers.google.com/my-business/content/basic-setup"
               target="_blank"><?php esc_html_e('Visit Google\'s Official Documentation', 'reviews-for-google-my-business'); ?></a>
        </p>
    </div>
</div>