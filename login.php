<?php

session_start();

include 'includes/db.php';

$message = "";

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users
            WHERE email='$email'";

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){

        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password'])){

            $_SESSION['user_id'] = $user['id'];

            $_SESSION['fullname'] = $user['fullname'];

            header("Location: dashboard.php");

            exit();
        }
    }

    $message = "Invalid Email or Password";
}

?>

<?php include 'includes/header.php'; ?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="brand-mark">⚡</span>
            <div>
                <h1>Smart Energy Tracker</h1>
            </div>
        </div>
        <h2>Login</h2>
        <p class="auth-message"><?php echo $message; ?></p>
        <form method="POST">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="your@email.com" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Password" required>

            <button type="submit" name="login">Login</button>
        </form>
        <p class="auth-footer">Don't have an account? <a href="register.php">Register</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>