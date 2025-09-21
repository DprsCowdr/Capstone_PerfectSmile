<?php
// Load URL helper
helper('url');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Perfect Smile Dental Clinic</title>
        <!-- logo-->
        <link rel="icon" type="image/x-icon" href="<?= base_url('img/img/pslogo.png') ?>" />
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="<?= base_url('css/styles.css') ?>" rel="stylesheet" />
        <!-- Custom theme CSS -->
        <link href="<?= base_url('css/customs.css') ?>" rel="stylesheet" />
    </head>

    <body id="page-top">
        <!-- Navigation-->        
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
            <div class="container">

            <a class="navbar-brand" href="#page-top">
             <img 
                src="<?= base_url('img/img/pslogo.png') ?>" 
                alt="Perfect Smile" 
                style="height: 42px; width: auto;" 
             />
            </a>

            <a class="navbar-brand" href="#page-top">
                <h4 style="text-shadow: 2px 2px 3px rgb(255, 255, 255);">
                    <span style="color: #6d3b9e;">Perfect</span> 
                    <span style="color: rgba(242, 0, 0, 0.821);">Smile</span>
                </h4>
            </a>
                <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span style="color: #f6f5f7; font-weight: bold; font-size: 16px;">Menu</span>
                    <i class="fas fa-bars ms-1" style="color: #eeebf1;"></i>
                </button>

            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#branches"><i class="fas fa-map-marker-alt me-1"></i>Branches</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services"><i class="fas fa-tooth me-1"></i>Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about"><i class="fas fa-info-circle me-1"></i>About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#appointment"><i class="fas fa-calendar-check me-1"></i>Book Appointment</a></li>
                    <li class="nav-item"><a class="nav-link" href="/login"><i class="fa fa-sign-in me-1"></i>Login</a></li>
                    
                </ul>
            </div>

            </div>
        </nav> 

        <!-- Masthead--> 
        <header class="masthead">
            <div class="container">
                <div class="masthead-subheading">
                    <!-- <span class="glow-word">Your</span> -->
                    <span class="glow-word">Perfect</span>
                    <span class="glow-word">Smile</span>
                    <!-- <span class="glow-word">Starts</span>
                    <span class="glow-word">Here.</span> -->
                    <div class="divider"></div>
                </div>
                <div class="masthead-heading text-uppercase">
                    <span class="glow-word">We Manage</span>
                    <span class="glow-word">your Oral</span>
                    <span class="glow-word">and Dental</span>
                    <span class="glow-word">care to</span>
                    <span class="glow-word">give</span>
                    <span class="glow-word">you back</span>
                    <span class="glow-word">your</span>
                    <span class="glow-word">Perfect Smile!</span>
                </div>
                <a class="btn btn-primary btn-xl text-uppercase" href="#appointment">Book Appointment</a>
                <br><br>
                <a class="btn btn-primary btn-xl text-uppercase" href="/login">User Login</a>
            </div>
        </header>
 
        <!-- Branches -->
         
            <section class="page-section" id="branches">
            <div class="container">
            <div class="blob blob-topleft"></div>
            <div class="blob blob-topright"></div>
            <div class="blob blob-bottomleft"></div>
            <div class="blob blob-bottomright"></div>

            <div class="text-center">
            <h2 class="section-heading text-uppercase">Our Branches</h2>
            <h3 class="section-subheading text-muted">Visit us at any of our locations.</h3>
            </div>
            <div class="row justify-content-center">
            
            <!-- Branch 1 -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-lg border-0 rounded-3 h-100">
                <img src="<?= base_url('img/img/irigabr.png') ?>" class="card-img-top" alt="Branch 1">
                <div class="card-body text-center">
                    <h4 class="card-title">Perfect Smile - Nabua Branch</h4>
                    <p class="card-text text-muted">
                    📍 ZONE 2, Brgy. Sto. Domingo, Nabua, Camarines Sur <br>
                    📞 0970-141-5022
                    </p>
                    <div class="social-links">
                        <a class="btn btn-dark btn-social" href="#!" aria-label="Diana Petersen Email Profile"><i class="fa fa-envelope"></i></a>
                        <a class="btn btn-dark btn-social" href="https://www.facebook.com/share/1JnFhvKYcB/?mibextid=wwXIfr" aria-label="Diana Petersen Facebook Profile"><i class="fab fa-facebook-f"></i></a>
                    </div>

                </div>
                </div>
            </div>

            <!-- Branch 2 -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-lg border-0 rounded-3 h-100">
                <img src="<?= base_url('img/img/irigabr.png') ?>" class="card-img-top" alt="Branch 2">
                <div class="card-body text-center">
                    <h4 class="card-title">Perfect Smile - Iriga City Branch </h4>
                    <p class="card-text text-muted">
                    📍 201 TANSYLIT BLDG ALFELOR ST. San Roque, Iriga City, Philippines <br>
                    📞 0946-060-6381
                    </p>
                    <div class="social-links">
                        <a class="btn btn-dark btn-social" href="mailto:minnierusiliagonowon@gmail.com" aria-label="Diana Petersen Email Profile"><i class="fa fa-envelope"></i></a>
                        <a class="btn btn-dark btn-social" href="https://www.facebook.com/share/1JnFhvKYcB/?mibextid=wwXIfr" aria-label="Diana Petersen Facebook Profile"><i class="fab fa-facebook-f"></i></a>
                    </div>

                </div>
                </div>
            </div>
            </div>
        </div>
        </section>

        <!-- Services Grid-->
        <section class="page-section bg-light" id="services">
            <div class="container">
                <div class="blob blob-left"></div>
                <div class="blob blob-topleft"></div>
                <div class="blob blob-bottomleft"></div>

                <div class="text-center">
                    <h2 class="section-heading text-uppercase">SERVICES</h2>
                    <h3 class="section-subheading text-muted">We Manage your Oral and Dental care to give you back your Perfect Smile!</h3>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 1-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal1">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/checkup.jpg') ?>" alt="DENTAL CHECKUP" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Dental Checkup</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 45 mins</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 2-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal2">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/braces.jpg') ?>" alt="BRACES" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Orthodontic Treatment</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 2-3 hours</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 3-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal3">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/cleaning.jpg') ?>" alt="DENTAL CLEANING" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Dental Cleaning</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 30 mins (max)</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 4-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal4">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/denture.jpg') ?>" alt="CROWNS AND DENTURES" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Crowns and Dentures</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 45 mins</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 5-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal5">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/rootCanal.jpg') ?>" alt="ROOT CANAL TREATMENT" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Root Canal Treatment</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 1 and 1/2 hours</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 6-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal6">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/toothExtract.jpg') ?>" alt="TOOTH EXTRACTION" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Tooth Extraction</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 30 mins</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 7-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal7">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/xray.jpg') ?>" alt="X-RAY" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">X-ray</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 15 mins</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 8-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal8">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/fluoride.jpg') ?>" alt="FLUORIDE TREATMENT" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Fluoride Treatment</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 5 mins</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 9-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal9">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/oralsurgery.jpg') ?>" alt="ORAL SURGERY" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Impacted Oral Surgery</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 2 hours</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 10-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal10">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/veneers.jpg') ?>" alt="VENEERS" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Veneers</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 20 mins</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 11-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal11">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/whitening.jpg') ?>" alt="TOOTH WHITENING" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Tooth Whitening</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 1 hour (max)</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 12-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal12">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/restoration.jpg') ?>" alt="TOOTH RESTORATION" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Tooth Restoration</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 20 mins</div>
                            </div>
                        </div>
                    </div>


                    <div class="col-lg-4 col-sm-6 mb-4">
                        <!-- services item 13-->
                        <div class="services-item">
                            <a class="services-link" data-bs-toggle="modal" href="#servicesModal13">
                                <div class="services-hover">
                                    <div class="services-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                </div>
                                <img class="img-fluid" src="<?= base_url('img/img/services/wisremoval.jpg') ?>" alt="WISDOM TOOTH REMOVAL" />
                            </a>
                            <div class="services-caption">
                                <div class="services-caption-heading">Wisdom Tooth Removal</div>
                                <div class="services-caption-subheading text-muted">Estimated Duration • 30 mins</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section class="page-section bg-light" id="about">
            <div class="container">
                <div class="blob blob-topright"></div>
                <div class="blob blob-bottomleft"></div>

                <div class="text-center">
                    <h2 class="section-heading text-uppercase">About Perfect Smile Dental Clinic</h2>
                    <h3 class="section-subheading text-muted">Our story and commitment to your smile.</h3>
                </div>

                <div class="row align-items-center">
                    <!-- Left Side: Dr. Minnie Card -->
                    <div class="col-lg-5 mb-4">
                        <div class="team-member text-center">
                            <div class="img-container">
                                <img class="mx-auto rounded-circle" 
                                    src="<?= base_url('img/img/team/2.jpg') ?>" 
                                    alt="Dr. Minnie R. Gonowon" />
                            </div>
                            <h4>Dr. Minnie R. Gonowon</h4>
                            <p class="text-muted">
                                <i class="fa fa-phone"></i> 0968 763 8940
                            </p>
                            <p class="text-muted small">Proprietor / Dentist</p>
                            <div class="social-links">
                                <a class="btn btn-dark btn-social" 
                                href="mailto:minnierusiliagonowon@gmail.com" 
                                aria-label="Minnie R. Gonowon Email Profile">
                                <i class="fa fa-envelope"></i></a>
                                <a class="btn btn-dark btn-social" 
                                href="https://www.facebook.com/profile.php?id=61573045293523" 
                                aria-label="Minnie R. Gonowon Facebook Profile">
                                <i class="fab fa-facebook-f"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: About Text (Styled with clinic-history box) -->
                    <div class="col-lg-7">
                        <div class="clinic-history">
                            <h5>Our Journey</h5>
                        <p>
                            <strong>Perfect Smile Dental Clinic</strong> was established in <strong>2019</strong> by 
                            <strong>Dr. Minnie R. Gonowon</strong>, its proprietor and resident dentist. Before founding 
                            the clinic, she honed her expertise in Saudi Arabia, where she gained valuable skills and 
                            experience in dentistry.
                        </p>
                        <p>
                            <strong>Smile</strong> has always been at the heart of Dr. Gonowon’s vision. With passion and 
                            dedication, she returned home to create a clinic that provides exceptional dental care in a 
                            warm, welcoming environment. Today, Perfect Smile is a trusted hub for preventive, restorative, 
                            and cosmetic treatments all devoted to bringing back your perfect smile.
                        </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- owner-->
        <!-- <section class="page-section bg-light" id="team">
            <div class="container">
                <div class="blob blob-topright"></div>
                <div class="blob blob-bottomleft"></div>

                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Owner and dentist of Perfect Smile Dental Clinic</h2>
                    <h3 class="section-subheading text-muted">Dedicated professionals committed to your dental health and comfort.</h3>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-4">
                        <div class="team-member">
                            <div class="img-container">
                                <img class="mx-auto rounded-circle" src="<?= base_url('img/img/team/1.jpg') ?>" alt="Dr. Parveen Anand" />
                            </div>
                            <h4>Dr. Parveen Anand</h4>
                            <p class="text-muted">
                                <i class="fa fa-phone"></i> 0968 763 8940
                            </p>
                            <p class="text-muted small">Proprietor</p>
                            <div class="social-links">
                                <a class="btn btn-dark btn-social" href="#!" aria-label="Parveen Anand Email Profile"><i class="fa fa-envelope"></i></a>
                                <a class="btn btn-dark btn-social" href="#!" aria-label="Parveen Anand Facebook Profile"><i class="fab fa-facebook-f"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="team-member">
                            <div class="img-container">
                                <img class="mx-auto rounded-circle" src="<?= base_url('img/img/team/2.jpg') ?>" alt="Dr. Minnie R. Gonowon" />
                            </div>
                            <h4>Dr. Minnie R. Gonowon</h4>
                            <p class="text-muted">
                                <i class="fa fa-phone"></i> 0968 763 8940
                            </p>
                            <p class="text-muted small">Proprietor/Dentist</p>
                            <div class="social-links">
                                <a class="btn btn-dark btn-social" href="mailto:minnierusiliagonowon@gmail.com" aria-label="Minnie R. Gonowon Email Profile"><i class="fa fa-envelope"></i></a>
                                <a class="btn btn-dark btn-social" href="https://www.facebook.com/profile.php?id=61573045293523" aria-label="Minnie R. Gonowon Facebook Profile"><i class="fab fa-facebook-f"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8 mx-auto text-center">
                <p class="large text-muted">
                    The Perfect Smile Dental Clinic is proudly managed by a dedicated couple. With one of the owners serving as the clinic’s skilled dentist, every treatment is provided with expert care and attention. Together, they ensure a welcoming, family-friendly environment where patients can receive routine checkups, cosmetic treatments, and advanced procedures—all aimed at giving back your perfect smile.
                </p>

                    </div>
                </div>
            </div>
        </section> -->

        <!-- Clients-->
        <!-- <div class="py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-3 col-sm-6 my-3">
                        <a href="#!"><img class="img-fluid img-brand d-block mx-auto" src="assets/img/logos/microsoft.svg" alt="..." aria-label="Microsoft Logo" /></a>
                    </div>
                    <div class="col-md-3 col-sm-6 my-3">
                        <a href="#!"><img class="img-fluid img-brand d-block mx-auto" src="assets/img/logos/google.svg" alt="..." aria-label="Google Logo" /></a>
                    </div>
                    <div class="col-md-3 col-sm-6 my-3">
                        <a href="#!"><img class="img-fluid img-brand d-block mx-auto" src="assets/img/logos/facebook.svg" alt="..." aria-label="Facebook Logo" /></a>
                    </div>
                    <div class="col-md-3 col-sm-6 my-3">
                        <a href="#!"><img class="img-fluid img-brand d-block mx-auto" src="assets/img/logos/ibm.svg" alt="..." aria-label="IBM Logo" /></a>
                    </div>
                </div>
            </div>
        </div>
     
      -->


        <!-- Appointment-->
        <section class="page-section" id="appointment">
            <!-- <div class="blob blob-top"></div> -->
            <!-- <div class="blob blob-bottom"></div> -->
            <!-- <div class="blob blob-left"></div>
            <div class="blob blob-right"></div> -->
            <!-- <div class="blob blob-topleft"></div> -->
            <div class="blob blob-topright"></div>
            <div class="blob blob-bottomleft"></div>
            <!-- <div class="blob blob-bottomright"></div> -->

                <div class="container">
                    <div class="text-center">
                        <h2 class="section-heading text-uppercase">Book an Appointment</h2>
                        <h3 class="section-subheading text-muted">Schedule your consultation with our expert team.</h3>
                    </div>
                    
                    <form id="appointmentForm">
                        <div class="row align-items-stretch mb-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input class="form-control" id="name" type="text" placeholder="Your Full Name *" required />
                                    <div class="invalid-feedback">Please enter your full name.</div>
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" id="email" type="email" placeholder="Your Email *" required />
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                                
                                <div class="form-group">
                                    <input class="form-control" id="phone" type="tel" placeholder="Your Phone Number *" required />
                                    <div class="invalid-feedback">Please enter your phone number.</div>
                                </div>
                                
                                <div class="form-group">
                                    <select class="form-select" id="service" required>
                                        <option value="">Select Service Type *</option>
                                        <option value="consultation">Initial Consultation</option>
                                        <option value="follow-up">Follow-up Appointment</option>
                                        <option value="emergency">Emergency Consultation</option>
                                        <option value="virtual">Virtual Meeting</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a service type.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input class="form-control" id="date" type="date" required />
                                    <div class="invalid-feedback">Please select an appointment date.</div>
                            </div>

                                <div class="form-group">
                                <label class="form-label" for="branch">Preferred Branch *</label>
                                <select class="form-select" id="branch" required>
                                    <option value="">Select Preferred Branch *</option>
                                    <option value="Iriga City">Iriga City Branch</option>
                                    <option value="Nabua">Nabua Branch</option>
                                </select>
                                <div class="invalid-feedback">Please select a preferred Branch.</div>
                                </div>
                                
                                <div class="form-group">
                                <label class="form-label" for="time">Preferred Time *</label>
                                <select class="form-select" id="time" required>
                                    <option value="">Select Preferred Time *</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                </select>
                                <div class="invalid-feedback">Please select a preferred time.</div>
                                </div>

                                
                                <div class="form-group form-group-textarea mb-md-0">
                                    <textarea class="form-control" id="notes" placeholder="Additional Notes or Special Requests" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Success message -->
                        <div class="d-none success-message" id="submitSuccessMessage">
                            <div class="text-center">
                                <div class="fw-bolder mb-2">Appointment Request Submitted Successfully!</div>
                                <p class="mb-0">We'll contact you within 24 hours to confirm your appointment details.</p>
                            </div>
                        </div>
                        
                        <!-- Error message -->
                        <div class="d-none error-message" id="submitErrorMessage">
                            <div class="text-center">
                                <div class="fw-bolder">Error submitting appointment request!</div>
                                <p class="mb-0">Please try again or contact us directly.</p>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="text-center">
                            <button class="btn btn-primary btn-xl text-uppercase" id="submitButton" type="submit">
                                Book Appointment
                            </button>
                        </div>
                    </form>
                </div>
        </section>

           
        <!-- Footer -->
        <footer class="footer py-5 bg-light border-top">
        <div class="container">
            <div class="row gy-4 align-items-start text-center text-md-start">

            <!-- Logo + Tagline -->
            <div class="col-md-4">
                <img src="<?= base_url('img/img/pslogo.png') ?>" alt="Perfect Smile Dental Clinic Logo" class="mb-3" style="max-width: 160px;">
                <p class="small text-muted">
                We Manage your Oral and Dental care to give you back your Perfect Smile!
                </p>
            </div>

            <!-- Branch Locations -->
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Our Branches</h6>
                <p class="small mb-2">📍 <strong>Iriga City Branch:</strong> 201 TANSYLIT BLDG ALFELOR ST. SAN ROQUE, Iriga City, Philippines</p>
                <p class="small mb-2">📍 <strong>Nabua Branch :</strong> ZONE 2, Brgy. Sto. Domingo, Nabua, Camarines Sur</p>
            </div>
            
            <!-- Contact + Social Links -->
            <div class="col-md-4 text-md-end">
                <h6 class="fw-bold mb-3">Contact Us</h6>
                <p class="small mb-1">📞 +63 946 060 6381 - Iriga Branch</p>
                <p class="small mb-1">📞 +63 970 141 5022 - Nabua Branch</p>
                <p class="small mb-2">✉️ perfectsmile@email.com</p>
                <div>
                <a class="btn btn-dark btn-social mx-1" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a class="btn btn-dark btn-social mx-1" href="mailto:perfectsmile@email.com" aria-label="Email"><i class="fa fa-envelope"></i></a>
                </div>
            </div>

            </div>

            <!-- Divider -->
            <hr class="my-4">

            <!-- Copyright -->
            <div class="text-center small text-muted">
            &copy; 2025 Perfect Smile Dental Clinic. All rights reserved.
            </div>
        </div>
        </footer>

    
     
        <!-- services Modals-->
        <!-- services item 1 modal popup-->
        <div class="services-modal modal fade" id="servicesModal1" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Service details-->
                                    <h2 class="text-uppercase">Dental Checkup</h2>
                                    <p class="item-intro text-muted">Routine care for a healthier smile.</p>
                                    <img class="img-fluid d-block mx-auto" src="<?= base_url('img/img/services/checkup.jpg') ?>" alt="Dental Checkup" />
                                    <p>
                                        A regular dental checkup is essential in maintaining strong teeth and healthy gums. 
                                        During the procedure, our dentist will carefully examine your mouth for cavities, plaque buildup, gum health, 
                                        and early signs of oral issues. Preventive care not only ensures oral hygiene but also helps in detecting 
                                        problems early before they become serious. 
                                        At Perfect Smile Dental Clinic, we make your comfort and care our top priority.
                                    </p>
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            30 - 60 minutes (depending on procedure)
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from 400 depending on procedure
                                        </li>
                                    </ul>
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- services item 2 modal popup-->
        <div class="services-modal modal fade" id="servicesModal2" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="close-modal" data-bs-dismiss="modal"><img src="<?= base_url('img/img/close-icon.svg') ?>" alt="Close modal" /></div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Project details-->
                                    <h2 class="text-uppercase">Orthodontic Treatment</h2>
                                    <p class="item-intro text-muted">Professional teeth alignment and bite correction services.</p>
                                    <img class="img-fluid d-block mx-auto" src="<?= base_url('img/img/services/braces.jpg') ?>" alt="Orthodontic Treatment Illustration" />
                                    <p>Our comprehensive orthodontic treatment offers modern solutions for teeth alignment and bite correction. We provide traditional metal braces, clear ceramic braces, and Invisalign clear aligners to suit your lifestyle and preferences. Our experienced orthodontists create personalized treatment plans to achieve optimal results, improving both the function and appearance of your smile. Treatment typically ranges from 12-24 months depending on individual needs and complexity.</p>
                                    <ul class="list-inline">

                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            12-24 months
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong><br>
                                    <b>Braces</b> Ranging from ₱50,000 to ₱70,000 depending on Xray status, In <b>Retainer</b> if  plain ₱4,000 and ₱4,500 with design.
                                        </li>
                                    </ul>
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- services item 3 modal popup-->
        <div class="services-modal modal fade" id="servicesModal3" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Service details-->
                                    <h2 class="text-uppercase">Dental Cleaning</h2>
                                    <p class="item-intro text-muted">Keep your teeth fresh, clean, and healthy.</p>
                                    
                                    <!-- Updated illustration -->
                                    <img class="img-fluid d-block mx-auto" src="<?= base_url('img/img/services/cleaning.jpg') ?>" alt="Dental Cleaning Illustration" />
                                    
                                    <p>
                                        Professional dental cleaning removes plaque, tartar, and stains that brushing and flossing alone 
                                        cannot eliminate. This procedure not only helps maintain oral hygiene but also prevents cavities, 
                                        gum disease, and bad breath. Our dental team ensures a gentle yet thorough cleaning process, 
                                        leaving your teeth polished and your smile healthier and brighter. 
                                        Regular cleaning is recommended every 6 months for optimal oral health.
                                    </p>
                                    
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            30 mins (max)
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱1,000 to ₱2,000 depending if mild or severe
                                        </li>
                                    </ul>
                                    
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- services item 4 modal popup-->
        <div class="services-modal modal fade" id="servicesModal4" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Service details-->
                                    <h2 class="text-uppercase">Crowns and Dentures</h2>
                                    <p class="item-intro text-muted">Restore your smile and chewing function with durable solutions.</p>
                                    
                                    <!-- Updated illustration -->
                                    <img class="img-fluid d-block mx-auto" src="<?= base_url('img/img/services/denture.jpg') ?>" alt="Crowns and Dentures Illustration" />
                                    
                                    <p>
                                        Crowns and dentures are effective restorative treatments designed to bring back the function 
                                        and aesthetics of your teeth. Dental crowns are used to cover and strengthen damaged or weak 
                                        teeth, while dentures replace missing teeth to restore chewing ability and improve overall 
                                        oral health. Our clinic provides high-quality, custom-fit crowns and dentures to ensure 
                                        comfort, durability, and a natural appearance for every patient.
                                    </p>
                                    
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            12-24 months
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱5,000 per unit if plastic and ₱8,000 per unit if porcelain.
                                        </li>
                                    </ul>
                                    
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- services item 5 modal popup-->
        <div class="services-modal modal fade" id="servicesModal5" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="<?= base_url('img/img/close-icon.svg') ?>" alt="Close modal" />
                    </div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Service details-->
                                    <h2 class="text-uppercase">Root Canal Treatment</h2>
                                    <p class="item-intro text-muted">Relieve pain and save your natural tooth with expert care.</p>

                                    <img class="img-fluid d-block mx-auto" src="<?= base_url('img/img/services/rootCanal.jpg') ?>" alt="Root Canal Treatment Illustration" />

                                    <p>
                                        A root canal treatment is a procedure used to repair and save a tooth that is badly decayed 
                                        or infected. During the treatment, the infected pulp is removed, and the inside of the tooth 
                                        is carefully cleaned, disinfected, and sealed. This prevents further infection while 
                                        relieving severe tooth pain. Our dental team ensures that the procedure is done with precision, 
                                        using modern techniques to make it as comfortable and painless as possible for our patients.
                                    </p>
                                    
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            12-24 months
                                        </li>    
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging to ₱8,000 depending on procedure.
                                        </li>
                                    </ul>
                                    
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- services item 6 modal popup-->
        <div class="services-modal modal fade" id="servicesModal6" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Service details-->
                                    <h2 class="text-uppercase">Tooth Extraction</h2>
                                    <p class="item-intro text-muted">Safe, gentle, and professional tooth removal.</p>
                                    <img class="img-fluid d-block mx-auto" src="<?= base_url('img/img/services/toothExtract.jpg') ?>" alt="Tooth Extraction Illustration" />
                                    <p>
                                        Tooth extraction is a common dental procedure performed when a tooth is severely damaged, decayed, 
                                        or causing crowding. At Perfect Smile Dental Clinic, we ensure the extraction process is done with 
                                        precision, using modern techniques and anesthesia for a safe and comfortable experience. 
                                        Whether it’s a simple extraction or a more complex surgical case, our priority is your comfort 
                                        and oral health recovery.
                                    </p>

                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            20-40 minutes
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                           <b>Molar Tooth Extraction</b> Ranging from ₱1,000 depending on procedure.
                                        </li>
                                    </ul>

                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- services item 7 modal popup-->
        <div class="services-modal modal fade" id="servicesModal7" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Close Button -->
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Modal Title -->
                                    <h2 class="text-uppercase">X-Ray</h2>
                                    <p class="item-intro text-muted">Quick and accurate dental imaging service.</p>
                                    
                                    <!-- Modal Image -->
                                    <img class="img-fluid d-block mx-auto shadow rounded-3" src="<?= base_url('img/img/services/xray.jpg') ?>" alt="Dental X-Ray Service" />
                                    
                                    <!-- Modal Description -->
                                    <p>
                                        Our X-Ray services provide clear and detailed imaging for accurate diagnosis of dental conditions. 
                                        With advanced equipment, we ensure safe and precise scans to help our dentists create the best treatment plan for you.
                                    </p>

                                    <!-- Modal Details -->
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            15 minutes
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱5,000 to ₱25,000 depending on procedure
                                        </li>
                                    </ul>

                                    <!-- Close Button -->
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- services item 8 modal popup-->
        <div class="services-modal modal fade" id="servicesModal8" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Close Button -->
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Modal Title -->
                                    <h2 class="text-uppercase">Fluoride Treatment</h2>
                                    <p class="item-intro text-muted">Strengthening teeth and preventing cavities with professional fluoride care.</p>
                                    
                                    <!-- Modal Image -->
                                    <img class="img-fluid d-block mx-auto shadow rounded-3" src="<?= base_url('img/img/services/fluoride.jpg') ?>" alt="Fluoride Treatment Service" />

                                    <!-- Modal Description -->
                                    <p>
                                        Our fluoride treatment helps to strengthen tooth enamel and prevent cavities. We use 
                                        professional-grade fluoride varnishes and gels to provide maximum protection for your 
                                        teeth. This treatment is quick, effective, and an essential part of maintaining optimal 
                                        oral health.
                                    </p>

                                    <!-- Modal Details -->
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            15-30 minutes
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱1,500 depending on procedure
                                        </li>
                                    </ul>
                                    <!-- Close Button -->
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- services item 9 modal popup-->
        <div class="services-modal modal fade" id="servicesModal9" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Close Button -->
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Modal Title -->
                                    <h2 class="text-uppercase">Impacted Oral Surgery</h2>
                                    <p class="item-intro text-muted">Specialized procedures to treat complex dental issues.</p>
                                    
                                    <!-- Modal Image -->
                                    <img class="img-fluid d-block mx-auto shadow rounded-3" src="<?= base_url('img/img/services/oralsurgery.jpg') ?>" alt="Oral Surgery Service" />
                                    
                                    <!-- Modal Description -->
                                    <p>
                                        Our oral surgery services address a range of dental and maxillofacial conditions, including 
                                        impacted wisdom teeth, jaw irregularities, cyst removal, and other complex oral health issues. 
                                        With advanced techniques and patient-focused care, we ensure safe, effective, and comfortable 
                                        procedures for long-term dental health.
                                    </p>

                                    <!-- Modal Details -->
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            12-24 months
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱15,000 depending on procedure
                                        </li>
                                    </ul>

                                    <!-- Close Button -->
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- services item 10 modal popup-->
        <div class="services-modal modal fade" id="servicesModal10" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Close Button -->
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Modal Title -->
                                    <h2 class="text-uppercase">Veneers</h2>
                                    <p class="item-intro text-muted">Enhance your smile with natural-looking dental veneers.</p>
                                    
                                    <!-- Modal Image -->
                                    <img class="img-fluid d-block mx-auto shadow rounded-3" src="<?= base_url('img/img/services/veneers.jpg') ?>" alt="Dental Veneers Service" />
                                    
                                    <!-- Modal Description -->
                                    <p>
                                        Veneers are thin, custom-made shells crafted from porcelain or composite material 
                                        designed to cover the front surface of teeth. They are ideal for improving the 
                                        appearance of teeth that are discolored, chipped, misaligned, or worn down. 
                                        Our veneer treatments are carefully tailored to match your natural smile, 
                                        giving you a brighter and more confident look.
                                    </p>

                                    <!-- Modal Details -->
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            12-24 months
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱5,000 to ₱25,000 depending on procedure
                                        </li>
                                    </ul>

                                    <!-- Close Button -->
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- services item 11 modal popup-->
        <div class="services-modal modal fade" id="servicesModal11" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Close Button -->
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Modal Title -->
                                    <h2 class="text-uppercase">Tooth Whitening</h2>
                                    <p class="item-intro text-muted">Brighten your smile with safe and effective whitening treatment.</p>
                                    
                                    <!-- Modal Image -->
                                    <img class="img-fluid d-block mx-auto shadow rounded-3" src="<?= base_url('img/img/services/whitening.jpg') ?>" alt="Tooth Whitening Service" />
                                    
                                    <!-- Modal Description -->
                                    <p>
                                        Tooth whitening is a cosmetic dental procedure that lightens teeth and helps 
                                        remove stains and discoloration. Our professional whitening treatment is designed 
                                        to give you noticeable results in just one session while being gentle on your enamel. 
                                        Whether your teeth are stained from coffee, tea, smoking, or natural aging, 
                                        this treatment restores a brighter, more confident smile.
                                    </p>

                                    <!-- Modal Details -->
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            12-24 months
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱10,000 depending on procedure
                                        </li>
                                    </ul>

                                    <!-- Close Button -->
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- services item 12 modal popup-->
        <div class="services-modal modal fade" id="servicesModal12" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Close Button -->
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Modal Title -->
                                    <h2 class="text-uppercase">Tooth Restoration</h2>
                                    <p class="item-intro text-muted">Restore your smile with our advanced tooth restoration treatments.</p>

                                    <!-- Modal Image -->
                                    <img class="img-fluid d-block mx-auto shadow rounded-3" src="<?= base_url('img/img/services/restoration.jpg') ?>" alt="Tooth Whitening Service" />
                                    
                                    <!-- Modal Description -->
                                    <p>
                                        Tooth restoration is a restorative dental procedure that repairs and rebuilds teeth damaged by decay, fractures, or wear. 
                                        Using tooth-colored materials such as composite resin, our treatment restores both the function and natural appearance of your teeth. 
                                        Whether you need a small filling or a more extensive repair, tooth restoration helps maintain oral health, prevents further damage, and gives you a stronger, healthier smile.
                                    </p>

                                    <!-- Modal Details -->
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            20 mins
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱1,000 per surface depending on deepness.
                                        </li>
                                    </ul>

                                    <!-- Close Button -->
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- services item 13 modal popup-->
        <div class="services-modal modal fade" id="servicesModal13" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Close Button -->
                    <div class="close-modal" data-bs-dismiss="modal">
                        <img src="img/img/close-icon.svg" alt="Close modal" />
                    </div>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="modal-body">
                                    <!-- Modal Title -->
                                    <h2 class="text-uppercase">Wisdom Tooth Removal</h2>
                                    <p class="item-intro text-muted">Safe and effective extraction for impacted or painful wisdom teeth.</p>
                                    
                                    <!-- Modal Image -->
                                    <img class="img-fluid d-block mx-auto shadow rounded-3" src="<?= base_url('img/img/services/wisremoval.jpg') ?>" alt="Wisdom Tooth Removal Service" />
                                    
                                    <!-- Modal Description -->
                                    <p>
                                        Wisdom tooth removal is a common oral surgery performed to address impacted, infected, 
                                        or overcrowded third molars. Our skilled dental professionals ensure a safe and 
                                        comfortable procedure using modern techniques and anesthesia to minimize discomfort. 
                                        Removing problematic wisdom teeth helps prevent infections, cysts, and alignment issues, 
                                        promoting long-term oral health.
                                    </p>

                                    <!-- Modal Details -->
                                    <ul class="list-inline">
                                        <li>
                                            <strong>Treatment Duration:</strong>
                                            12-24 months
                                        </li>
                                        <li>
                                            <strong>Service Fee:</strong>
                                            Ranging from ₱5,000 to ₱25,000 depending on procedure
                                        </li>
                                    </ul>

                                    <!-- Close Button -->
                                    <button class="btn btn-primary btn-xl text-uppercase" data-bs-dismiss="modal" type="button">
                                        <i class="fas fa-xmark me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="<?= base_url('js/scripts.js') ?>"></script>
        <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
        <!-- * *                               SB Forms JS                               * *-->
        <!-- * * Activate your form at https://startbootstrap.com/solution/contact-forms * *-->
        <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
