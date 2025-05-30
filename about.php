<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Company Name</title>
    <link rel="stylesheet" href="about.css">
</head>
<body>

    <section class="hero">
        <div class="container">
            <h1>About Us</h1>
            <p>Learn about our story, our mission, and the team behind our success.</p>
        </div>
    </section>

    <section class="about-content">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2>Our Story</h2>
                    <p>Founded in 2025, Relux.co began with a simple mission: to provide exceptional products and services that make a difference in people's lives. What started as a small team with big dreams has grown into a thriving company with clients worldwide.</p>
                    
                    <h2>Our Mission</h2>
                    <p>We're dedicated to delivering innovative solutions that address real-world challenges. Through creativity, integrity, and commitment to excellence, we strive to exceed expectations and create lasting value for our clients and communities.</p>
                    
                    <h2>Our Values</h2>
                    <ul>
                        <li><strong>Innovation:</strong> We embrace new ideas and technologies to drive progress.</li>
                        <li><strong>Integrity:</strong> We conduct business with honesty, transparency, and ethical standards.</li>
                        <li><strong>Excellence:</strong> We pursue the highest quality in everything we do.</li>
                        <li><strong>Collaboration:</strong> We believe in the power of teamwork and partnerships.</li>
                        <li><strong>Impact:</strong> We measure success by the positive difference we make.</li>
                    </ul>
                </div>
                
                <div class="about-image">
                    <!-- Replace the placeholder with an actual image -->
                    <img src="images/company-image.png" alt="Our Company" class="company-image">
                </div>
            </div>
            
            <div class="team-section">
                <h2>Meet Our Team</h2>
                <div class="team-grid">
                    <?php
                    // Team members with image filenames
                    $team_members = [
                        [
                            'name' => 'Jane Doe',
                            'position' => 'CEO & Founder',
                            'bio' => 'With over 15 years of industry experience, Jane leads our company vision and strategy.',
                            'image' => 'images/jane-doe.png'
                        ],
                        [
                            'name' => 'John Smith',
                            'position' => 'Technical Director',
                            'bio' => 'John oversees all technical operations and innovations, bringing 10+ years of expertise.',
                            'image' => 'images/john-smith.png'
                        ],
                        [
                            'name' => 'Emily Chen',
                            'position' => 'Creative Lead',
                            'bio' => 'Emily brings creativity and fresh perspectives to every project we undertake.',
                            'image' => 'images/emily-chen.png'
                        ],
                        [
                            'name' => 'Michael Johnson',
                            'position' => 'Client Relations',
                            'bio' => 'Michael ensures our clients receive exceptional service and support throughout their journey with us.',
                            'image' => 'images/michael-johnson.png'
                        ]
                    ];
                    
                    foreach($team_members as $member) {
                        echo '<div class="team-member">';
                        // Replace the empty div with an actual image
                        echo '<img src="' . htmlspecialchars($member['image']) . '" alt="' . htmlspecialchars($member['name']) . '" class="member-photo">';
                        echo '<h3>' . htmlspecialchars($member['name']) . '</h3>';
                        echo '<p class="position">' . htmlspecialchars($member['position']) . '</p>';
                        echo '<p>' . htmlspecialchars($member['bio']) . '</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
    
    <section class="testimonials">
        <div class="container">
            <h2>What Our Clients Say</h2>
            <div class="testimonial-grid">
                <?php
                // Testimonials could also be loaded from a database
                $testimonials = [
                    [
                        'quote' => 'Working with this team transformed our business. Their expertise and dedication are unmatched in the industry.',
                        'author' => 'Sarah Williams, CEO of TechStart'
                    ],
                    [
                        'quote' => 'The solutions provided exceeded our expectations. I highly recommend their services to anyone looking for innovation and reliability.',
                        'author' => 'Robert Chen, Director at Global Innovations'
                    ],
                    [
                        'quote' => 'From start to finish, the process was smooth and professional. The results speak for themselves.',
                        'author' => 'Amanda Lopez, Small Business Owner'
                    ]
                ];
                
                foreach($testimonials as $testimonial) {
                    echo '<div class="testimonial">';
                    echo '<blockquote>"' . htmlspecialchars($testimonial['quote']) . '"</blockquote>';
                    echo '<p class="author">— ' . htmlspecialchars($testimonial['author']) . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>Relux.co</h3>
                    <p>Making a difference since 2025</p>
                </div>

                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p>123 Business Street<br>City, State 12345</p>
                    <p>Phone: (123) 456-7890<br>Email: info@companyname.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Company Name. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>