<?php
include "db_connect.php";
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Content - DiabetesMonitor</title>
    <link rel="stylesheet" href="styles1.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">DiabetesMonitor</div>
        </nav>
    </header>
    
    <div id="app-container">
        <aside>
            <ul>
                <li><a href="healthdata.php"><span class="icon">📝</span> Health Data Input</a></li>
                <li><a href="ai_health_analysis.php"><span class="icon">🤖</span> AI Health Analysis</a></li>
                <li><a href="diet.php"><span class="icon">🍎</span> Personalized Diet Plans</a></li>
                <li class="active"><a href="educational.php"><span class="icon">📚</span> Educational Content</a></li>
                <li><a href="#"><span class="icon">📊</span> Dashboard</a></li>
            </ul>
        </aside>
        
        <main>
            <h1>Educational Content</h1>
            
            <section id="info-banner">
                <h2>Understanding Diabetes</h2>
                <p>Browse our collection of educational resources to learn more about diabetes management, treatment options, and healthy living tips.</p>
            </section>
            
            <section id="articles">
                <h2>Featured Articles</h2>
                
                <div class="article-grid">
                    <article>
                        <h3>What is Type 1 Diabetes?</h3>
                        <p>Type 1 diabetes is an autoimmune condition where the pancreas produces little or no insulin. Learn about causes, symptoms, and management strategies.</p>
                        <a href="https://www.mayoclinic.org/diseases-conditions/type-1-diabetes/symptoms-causes/syc-20353011" class="read-more">Read More</a>
                    </article>
                    
                    <article>
                        <h3>Type 2 Diabetes: Risk Factors and Prevention</h3>
                        <p>Explore the risk factors for type 2 diabetes and discover lifestyle changes that can help prevent or delay its onset.</p>
                        <a href="https://www.mayoclinic.org/diseases-conditions/type-2-diabetes/symptoms-causes/syc-20351193" class="read-more">Read More</a>
                    </article>
                    
                    <article>
                        <h3>The Importance of Regular Blood Sugar Monitoring</h3>
                        <p>Learn why tracking your blood glucose levels is essential for effective diabetes management and how to establish a monitoring routine.</p>
                        <a href="https://my.clevelandclinic.org/health/treatments/17956-blood-sugar-monitoring" class="read-more">Read More</a>
                    </article>
                    
                    <article>
                        <h3>Healthy Eating for Diabetes Management</h3>
                        <p>Discover dietary guidelines and meal planning strategies to help control blood sugar levels while enjoying a variety of nutritious foods.</p>
                        <a href="https://www.webmd.com/diabetes/diabetic-food-list-best-worst-foods" class="read-more">Read More</a>
                    </article>
                </div>
            </section>
            
            <section id="videos">
                <h2>Educational Videos</h2>
                
                <div class="video-grid">
                  <article class="video">
                    <a href="https://www.youtube.com/watch?v=7z46WzzWf4I" class="video-link">
                        <div class="video-placeholder">
                            <img src="picture/diabetes2.jpg" alt="Diabetes explanation video">
                            <div class="play-button"></div>
                        </div>
                    </a>
                    <h3>Understanding Diabetes: The Basics</h3>
                    <p>A comprehensive overview of what diabetes is and how it affects your body.</p>
                </article>
                    
                <article class="video">
                  <a href="https://www.youtube.com/watch?v=HZH4QASD3ss" class="video-link">
                      <div class="video-placeholder">
                          <img src="picture/diabetes1.jpg" alt="Diabetes explanation video">
                          <div class="play-button"></div>
                      </div>
                  </a>
                  <h3>Understanding Diabetes: The Basics</h3>
                  <p>A comprehensive overview of what diabetes is and how it affects your body.</p>
              </article>
                    
              <article class="video">
                <a href="https://www.youtube.com/watch?v=En2oDkKC6Ms" class="video-link">
                    <div class="video-placeholder">
                        <img src="picture/test.jpg" alt="Diabetes explanation video">
                        <div class="play-button"></div>
                    </div>
                </a>
                <h3>Understanding Diabetes: The Basics</h3>
                <p>A comprehensive overview of what diabetes is and how it affects your body.</p>
            </article>
            </section>
            
            <section id="resources">
                <h2>Additional Resources</h2>
                <ul>
                    <li><a href="#">American Diabetes Association</a> - Guidelines and resources for diabetes management</li>
                    <li><a href="#">Diabetes Self-Management Tools</a> - Apps and devices to help monitor your condition</li>
                    <li><a href="#">Diabetes Support Groups</a> - Connect with others living with diabetes</li>
                    <li><a href="#">Diabetes and Nutrition Guide</a> - Comprehensive food guide for diabetic patients</li>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>