<?php

include 'includes/db.php';

$message = "";

if(isset($_POST['register'])){

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $sql = "INSERT INTO users(fullname, email, password)

            VALUES(
                '$fullname',
                '$email',
                '$password'
            )";

    if(mysqli_query($conn, $sql)){

        $message = "Registration Successful";

    } else {

        $message = "Error Occurred";

    }
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
        <h2>Register</h2>
        <p class="auth-message"><?php echo $message; ?></p>
        <form method="POST">
            <label for="fullname">Full Name</label>
            <input id="fullname" type="text" name="fullname" placeholder="John Doe" required>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="your@email.com" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Password" required>

            <button type="submit" name="register">Register</button>
        </form>
        <p class="auth-footer">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>