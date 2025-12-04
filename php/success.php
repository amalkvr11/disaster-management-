<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registration Successful</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: "Poppins", sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        margin: 0;
    }

    .box {
        background: rgba(255, 255, 255, 0.15);
        padding: 40px 50px;
        border-radius: 25px;
        backdrop-filter: blur(18px);
        text-align: center;
        width: 90%;
        max-width: 450px;
        animation: fadeIn 1s ease-out;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    h1 {
        font-size: 2.2rem;
        margin-bottom: 15px;
        font-weight: 600;
        text-shadow: 0 3px 8px rgba(0,0,0,0.4);
    }

    p {
        font-size: 1.1rem;
        margin-bottom: 25px;
    }

    a {
        display: inline-block;
        padding: 14px 25px;
        background: #ffffff;
        color: #764ba2;
        font-weight: 600;
        border-radius: 20px;
        text-decoration: none;
        transition: 0.3s;
        font-size: 1rem;
    }

    a:hover {
        background: #f3e8ff;
        transform: translateY(-3px);
    }
</style>
</head>
<body>

<div class="box">
    <h1>🎉 Registration Successful!</h1>
    <p>Thank you for joining as a volunteer.  
    Your support can make a real difference.</p>

    <a href="/disaster-management-/html/newVolunteer.html">Register Another</a>
</div>

</body>
</html>
