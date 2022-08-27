<form method="POST" class="my-5">
    <h2>Please log in to continue.</h2>

    <?php
    $loginError = $account->getLoginError();
    if ($loginError) {
        // Clear the error so it won't be displayed again.
        $account->clearLoginError();
        
        // Render error alert.
        ?>
        <div class="alert alert-danger">
            <?php echo($loginError); ?>
        </div>
        <?php
    }
    ?>
    
    <div class="mb-3">
        <label for="login-form__username" class="form-label">Username</label>
        <input class="form-control" id="login-form__username" name="username">
    </div>
    
    
    <div class="mb-3">
        <label for="login-form__password" class="form-label">Password</label>
        <input class="form-control" id="login-form__password" name="password" type="password">
    </div>

    <div class="btn-group">
        <button class="btn btn-primary">Log In</button>
    </div>
</form>
