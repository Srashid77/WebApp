<?php
include "db_connect.php";
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiabetesMonitor - Smart Diabetes Monitoring Made Simple</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo-container">
            <div class="logo">
                <span class="logo-letter">D</span>
            </div>
            <span class="logo-text">DiabetesMonitor</span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about_diabetes.php" class="active">About Diabetes</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </nav>
        <div class="cta-button">
            <a href="login.php" class="btn btn-primary">Get Started</a>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Warning <span class="highlight">Signs</span> and Symptoms</h1>
                <p>Know the warning signs and symptoms of diabetes and diabetes complications so that you can take action to improve your health.</p>
            </div>
            <div class="hero-image">
                <img src="picture/test.jpg" alt="Diabetes Management" />
            </div>
        </section>
        
        
        <section class="diabetes-info">
    <div class="symptoms-section">
      <h1 class="intro-text">
       The following symptoms of diabetes are typical. However, some people with diabetes have symptoms so mild that they go unnoticed.
       </h1>
        <h2 class="symptoms-heading">Common symptoms of diabetes:</h2>
        <div class="symptoms-content">
            <ul class="symptoms-list">
                <li>Urinating often</li>
                <li>Feeling very thirsty</li>
                <li>Feeling very hungry—even though you are eating</li>
                <li>Extreme fatigue</li>
                <li>Blurry vision</li>
                <li>Cuts/bruises that are slow to heal</li>
                <li>Weight loss—even though you are eating more (type 1)</li>
                <li>Tingling, pain, or numbness in the hands/feet (type 2)</li>
            </ul>
            <div class="symptoms-image">
                <img src="picture/test.jpg" alt="Diabetes symptoms illustration">
            </div>
        </div>
    </div>
</section>

            
            <div class="diabetes-types">
                <h2>Types of Diabetes</h2>
                
                <div class="diabetes-type">
                    <h3>Type 1 Diabetes</h3>
                    <p>Type 1 diabetes is thought to be caused by an autoimmune reaction that stops your body from making insulin. About 5-10% of people with diabetes have type 1. Symptoms often develop quickly and can be severe.</p>
                    <p>People with type 1 diabetes need to take insulin every day to survive. Currently, no one knows how to prevent type 1 diabetes.</p>
                </div>
                
                <div class="diabetes-type">
                    <h3>Type 2 Diabetes</h3>
                    <p>With type 2 diabetes, your body doesn't use insulin well and can't keep blood sugar at normal levels. About 90-95% of people with diabetes have type 2, which develops over many years and is usually diagnosed in adults.</p>
                    <p>Type 2 diabetes can be prevented or delayed with healthy lifestyle changes, such as losing weight if you're overweight, eating healthier, and getting regular physical activity.</p>
                </div>
                
                <div class="diabetes-type">
                    <h3>Gestational Diabetes</h3>
                    <p>Gestational diabetes develops in pregnant women who have never had diabetes. If you have gestational diabetes, your baby could be at higher risk for health problems.</p>
                    <p>Gestational diabetes usually goes away after your baby is born but increases your risk for type 2 diabetes later in life.</p>
                </div>
                
                <div class="diabetes-type">
                    <h3>Prediabetes</h3>
                    <p>In the United States, 96 million adults—more than 1 in 3—have prediabetes. With prediabetes, blood sugar levels are higher than normal, but not high enough for a type 2 diabetes diagnosis.</p>
                    <p>Prediabetes raises your risk for type 2 diabetes, heart disease, and stroke. The good news is if you have prediabetes, a CDC-recognized lifestyle change program can help you take healthy steps to reverse it.</p>
                </div>
            </div>
            
            <div class="complications-section">
                <h2>Potential Complications</h2>
                <p>If not properly managed, diabetes can lead to serious complications including:</p>
                
                <ul class="complications-list">
                    <li>
                        <h4>Heart disease and stroke</h4>
                        <p>People with diabetes are twice as likely to have heart disease or stroke compared to those without diabetes.</p>
                    </li>
                    <li>
                        <h4>Kidney disease</h4>
                        <p>Diabetes is the leading cause of kidney failure, accounting for approximately 44% of new cases.</p>
                    </li>
                    <li>
                        <h4>Eye problems</h4>
                        <p>Diabetic retinopathy can cause vision loss and blindness in people with diabetes.</p>
                    </li>
                    <li>
                        <h4>Neuropathy (nerve damage)</h4>
                        <p>About 60-70% of people with diabetes have some form of nerve damage, which can lead to pain, tingling, and loss of feeling.</p>
                    </li>
                    <li>
                        <h4>Foot problems</h4>
                        <p>Nerve damage and poor blood flow can lead to serious foot problems, sometimes requiring amputation.</p>
                    </li>
                </ul>
            </div>
            <section class="faq-hero">
    <section class="faq-content">
        <div class="container">
            <div class="faq-grid">
                <!-- Left Column: Photo Attachment Placeholder -->
                <div class="faq-photo">
                    <!-- Placeholder for photo to be added -->
                    <div class="photo-placeholder">
                        <img src="picture/insulin.jpg" alt="Diabetes Management" />
                    </div>
                </div>

                <!-- Right Column: Managing Diabetes -->
                <div class="faq-managing-diabetes">
                    <h2>Managing Diabetes</h2>
                    <p>Managing diabetes means maintaining blood glucose levels within the target range. This involves:</p>
                    <ul>
                        <li>Regular monitoring of blood glucose levels</li>
                        <li>Taking medications as prescribed</li>
                        <li>Following a healthy eating plan</li>
                        <li>Engaging in regular physical activity</li>
                        <li>Maintaining a healthy weight</li>
                        <li>Regular medical check-ups</li>
                    </ul>
                    <p>Always consult with healthcare professionals for personalized advice on managing diabetes.</p>
                </div>
            </div>
        </div>
    </section>
</section>

    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <p>© 2025 DiabetesMonitor. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>