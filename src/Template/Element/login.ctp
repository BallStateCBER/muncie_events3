<div id="login">
    <?= $this->Form->create('User', ['url' => ['controller' => 'Users', 'action' => 'login']]); ?>
    <div class='form-group col-lg-4 col-xs-12'>
        <?= $this->Form->control('email', ['class' => 'form-control']); ?>
    </div>
    <div class='form-group col-lg-4 col-xs-12'>
        <?= $this->Form->control('password', ['class' => 'form-control']); ?>
        <?= $this->Html->link(__('Forgot password?'), ['controller' => 'Users', 'action' => 'forgotPassword'], ['class' => 'nav-link']); ?>
    </div>
    <div class="form-group col-lg-4 col-xs-12">
        <?= $this->Form->input('remember_me', [
                'type' => 'checkbox',
                'label' => [
                    'text' => ' Remember me',
                    'style' => 'display: inline;'
                ],
                'checked' => true
            ]);
        ?>
    </div>
    <?= $this->Form->button(__('Login'), ['class' => 'btn btn-secondary btn-sm']); ?>
    <?= $this->Form->end() ?>
    Or log in with Facebook: <?= $this->Facebook->loginLink([
        'label' => 'Log in with Facebook',
        'img' => 'fb_login.png',
        'show-faces' => false,
        'perms' => 'email,user_events,create_event,rsvp_event',
        'redirect' => "/users/confirm_facebook_login"
    ]); ?>
</div>

Don't have an account yet?

<?= $this->Html->link(
    'Register',
    [
        'controller' => 'users',
        'action' => 'register'
    ]
); ?>
