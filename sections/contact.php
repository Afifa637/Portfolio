<?php
if (!isset($is_included)) {
    $page_title = "Contact | Afifa Sultana";
}
?>
<!-- Contact Section -->
<section class="contact section" id="contact">
      <h3 class="section-title"><i class="fa-regular fa-address-book"></i> Contact <span>Me</span></h3>

      <div class="contact-container container grid">
        <div class="contact-content">
          <div class="contact-card">
            <span class="contact-icon">
                <i class="fa-solid fa-map-location-dot"></i>
            </span>
            <div class = "contact-info">
                <h3 class="contact-title">Address</h3>
                <p class="contact-data">Rokeya Hall, KUET, Khulna</p>
            </div>
          </div>

          <div class="contact-card">
            <span class="contact-icon">
                <i class="fa-solid fa-envelope"></i>
            </span>
            <div class = "contact-info">
                <h3 class="contact-title">Email</h3>
                <p class="contact-data">afifasultana637@gmail.com</p>
            </div>
          </div>

          <div class="contact-card">
            <span class="contact-icon">
                <i class="fa-brands fa-whatsapp"></i>
            </span>
            <div class = "contact-info">
                <h3 class="contact-title">WhatsApp</h3>
                <p class="contact-data">01625738164</p>
            </div>
          </div>

        </div>
        <form action="#" class="contact-form grid">
            <div class="contact-form-group grid">
              <div class="contact-form-div">
                <label for="" class="contact-form-label">Your Name<b>*</b></label>
                <input type="text" class="contact-form-input" id="contact-name" placeholder="Enter your name" required />
              </div>

              <div class="contact-form-div">
                <label for="" class="contact-form-label">Your Email Address<b>*</b></label>
                <input type="email" class="contact-form-input" id="contact-email" placeholder="Enter your name" required />
              </div>
            </div>

            <div class="contact-form-div">
              <label for="" class="contact-form-label">Your Subject<b>*</b></label>
              <input type="text" class="contact-form-input" id="contact-subject" placeholder="Enter your Subject" required />
            </div>

            <div class="contact-form-div">
              <label for="" class="contact-form-label">Your Message<b>*</b></label>
              <textarea class="contact-form-input contact-form-area" id="contact-message" placeholder="Enter your message" required></textarea>
            </div>

            <div class="contact-submit">
              <span>* Accept the terms and conditions.</span>
              <button type="submit" class="btn">
                <i class="fa-solid fa-paper-plane"></i> Send Message
              </button>
            </div>

            <p class="message" id="message"></p>
        </form>

        <div class="section-deco deco-left">
          <img src="assets\images\deco1.png" alt="" class="shape" />
        </div>
      </div>
    </section>

