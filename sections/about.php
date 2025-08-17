<?php
if (!isset($is_included)) {
    $page_title = "About | Afifa Sultana";
}
?>

<!-- About Section -->
<section class="about section" id="about">
  <div class="about-container container">
    
    <!-- Section Title -->
    <h2 class="section-title">About <span>Me</span></h2>

    <div class="about-content-wrapper">
      <!-- Profile Image -->
      <div class="about-img">
        <img src="assets/images/profile1.png" alt="Afifa Sultana Profile" class="about-profile" />
      </div>

      <!-- Content -->
      <div class="about-content">
        <p>
          Hello! I’m <strong>Afifa Sultana</strong>, an aspiring 
          <span class="highlight">Software Engineer</span> passionate about 
          crafting seamless user experiences and building impactful 
          applications. I love working with modern technologies and 
          translating ideas into elegant solutions.
        </p>

        <!-- Read More Content -->
        <div class="read-more-content" id="moreText">
          <p>
            My journey started as a curious learner exploring 
            <em>web development</em>, mobile applications, and 
            problem-solving with algorithms. Over time, I’ve developed 
            strong foundations in <strong>HTML, CSS, JavaScript, PHP, 
            C++, Java, Kotlin, Flutter</strong>, and frameworks like 
            <strong>React Native & Laravel</strong>.
          </p>
          <p>
            Beyond coding, I am highly motivated by challenges and teamwork. 
            I believe in <span class="highlight">continuous learning</span>, 
            exploring innovative solutions, and contributing to projects that 
            create a real difference.
          </p>
          <p>
            ✨ Fun fact: I enjoy blending design and logic, making my projects 
            not only functional but also visually appealing.  
            🌱 Currently, I’m exploring <strong>Kotlin Multiplatform (KMP)</strong> 
            and advanced <strong>AI/ML integrations</strong>.
          </p>
        </div>

        <!-- Read More Button -->
        <button class="btn" id="readMoreBtn" onclick="toggleReadMore()">Read More</button>

      </div>
    </div>

    <!-- Decorative Shapes -->
    <div class="section-deco deco-right">
      <img src="assets/images/deco2.png" alt="" class="shape" />
    </div>

  </div>
</section>
