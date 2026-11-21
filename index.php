<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="تعرّف على الوجهات السياحية في السعودية ومعالمها التاريخية والطبيعية والثقافية.">
  <title>اكتشف السعودية | وجهات سياحية</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body id="top">

<?php require __DIR__ . '/navigation.php'; ?>

  <main id="main-content">
    <section class="hero">
      <div class="hero-inner">
        <div class="hero-copy">
          <h1 id="hero-title"><span>اكتشف</span> السعودية</h1>
          <div class="hero-rule"></div>
          <p class="hero-description">تعرّف على أبرز الوجهات السياحية في المملكة، من المواقع التاريخية إلى الطبيعة والمدن الحديثة.</p>
          <a class="button button-sand" href="regions.php">استكشف الوجهات <span>←</span></a>
        </div>
      </div>
      <div class="hero-bottom">
        <a href="#why">اكتشف المزيد <span>↓</span></a>
        <p><span>✦</span> المملكة العربية السعودية</p>
      </div>
    </section>

    <section class="why section-wrap" id="why">
      <div class="why-story">
        <div class="why-copy">
          <p class="eyebrow">أكثر من وجهة</p>
          <h2 id="why-title">لماذا <span>السعودية؟</span></h2>
          <p class="section-description">تتميز المملكة بتنوع وجهاتها السياحية، حيث تجمع بين المواقع التاريخية والمناظر الطبيعية في مناطق مختلفة.</p>
          <p id="why-more" class="extra-copy">من المرتفعات الجبلية إلى المدن التاريخية والسواحل، تمنحك كل وجهة فرصة لاكتشاف جانب مختلف من المملكة.</p>
          <button class="text-button js-only" type="button" id="why-toggle">عرض أقل <span>−</span></button>
        </div>
        <figure class="heritage-photo">
          <img src="assets/images/turaif.jpg" alt="مبانٍ طينية تاريخية وأبراج بالعمارة النجدية في حي الطريف بالدرعية">
          <figcaption><span>العمارة النجدية</span><strong>حي الطريف · الدرعية</strong></figcaption>
        </figure>
      </div>

      <div class="feature-list">
        <article class="feature">
          <img src="assets/mountain.svg" alt="" width="48" height="48">
          <h3>طبيعة متنوّعة</h3>
          <p>جبال وصحارى وشواطئ تناسب اهتمامات متنوّعة.</p>
        </article>
        <article class="feature">
          <img src="assets/heritage.svg" alt="" width="48" height="48">
          <h3>معالم تاريخية</h3>
          <p>مواقع أثرية وأحياء تراثية للتعرّف على تاريخ المملكة.</p>
        </article>
        <article class="feature">
          <img src="assets/palm.svg" alt="" width="48" height="48">
          <h3>مدن ومعالم حديثة</h3>
          <p>مدن تجمع بين التطور العمراني والمعالم الحديثة، مثل الرياض والظهران.</p>
        </article>
      </div>
    </section>

    <section class="destinations" id="destinations">
      <div class="section-wrap">
        <div class="section-heading">
          <div><h2 id="destinations-title">وجهات <span>مختارة</span></h2></div>
          <p>تعرّف على ثلاث من وجهات المملكة التاريخية والطبيعية.</p>
        </div>

        <div class="destination-list">
          <article class="destination-card alula-card" id="alula">
            <div class="destination-image"><img class="destination-photo" src="assets/images/alula-new.png" alt="صخرة الفيل في العلا عند غروب الشمس"></div>
            <div class="card-copy">
              <div class="card-meta"><span>التاريخ والطبيعة</span></div>
              <h3>العلا</h3>
              <p>وجهة تاريخية تشتهر بموقع الحِجر وتكويناتها الصخرية الطبيعية.</p>
              <a class="featured-link" href="destination.php?id=6">استكشف الوجهة <span>←</span></a>
            </div>
          </article>

          <article class="destination-card diriyah-card" id="diriyah">
            <div class="destination-image"><img class="destination-photo" src="assets/images/diriyah-new.png" alt="مبانٍ طينية وأبراج بالعمارة النجدية في الدرعية أمام أشجار النخيل"></div>
            <div class="card-copy">
              <div class="card-meta"><span>التراث والثقافة</span></div>
              <h3>الدرعية</h3>
              <p>مدينة تاريخية تشتهر بحي الطريف وعمارتها النجدية التقليدية.</p>
              <a class="featured-link" href="destination.php?id=2">استكشف الوجهة <span>←</span></a>
            </div>
          </article>

          <article class="destination-card soudah-card" id="soudah">
            <div class="destination-image"><img class="destination-photo" src="assets/images/alsoudah-new.png" alt="مرتفعات السودة الجبلية وسط الغيوم والضباب في منطقة عسير."></div>
            <div class="card-copy">
              <div class="card-meta"><span>الطبيعة والجبال</span></div>
              <h3>السودة</h3>
              <p>وجهة جبلية في منطقة عسير، تشتهر بمرتفعاتها العالية وطبيعتها الخضراء وأجوائها الضبابية.</p>
              <a class="featured-link" href="destination.php?id=12">استكشف الوجهة <span>←</span></a>
            </div>
          </article>

        </div>
        <div class="all-destinations"><a class="button" href="regions.php">اكتشف جميع الوجهات <span>←</span></a></div>
      </div>
    </section>

    <section class="about section-wrap" id="about">
      <div class="about-title"><p class="eyebrow">عن اكتشف السعودية</p><h2 id="about-title">عن <span>الموقع</span></h2></div>
      <div class="about-copy"><p>يتيح لك الموقع تصفّح وجهات سياحية في مختلف مناطق المملكة، والتعرّف على مواقعها وأبرز معالمها.</p></div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-main section-wrap">
      <div><a class="brand footer-brand" href="#top"><img src="assets/palm.svg" alt="" width="44" height="54"><span>اكتشف السعودية<small>وجهات المملكة</small></span></a><p>دليلك للتعرّف على وجهات المملكة.</p></div>
      <nav><h2>روابط مفيدة</h2><a href="#why">لماذا السعودية؟</a><a href="#about">عن الموقع</a></nav>
      <div class="footer-actions"><a class="back-top" href="#top">العودة إلى الأعلى <span>↑</span></a></div>
    </div>
    <div class="footer-bottom section-wrap"><p>اكتشف السعودية © ٢٠٢٦</p></div>
  </footer>
  <script src="script.js"></script>
</body>
</html>
