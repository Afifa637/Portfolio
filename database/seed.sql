-- ===========================================================================
-- Seed data
-- ---------------------------------------------------------------------------
-- Mirrors config/profile.php so the admin panel opens with the same content
-- the site already shows, rather than empty forms.
--
--   mysql -u root -p portfolio_db < database/seed.sql
--
-- Re-running is safe: each block clears its table first.
--
-- IMPORTANT: the admin account below has no usable password. Create your own
-- with a bcrypt hash before exposing /admin — see the bottom of this file.
-- ===========================================================================

SET NAMES utf8mb4;


-- ------------------------------------------------------------ home_info ---

TRUNCATE TABLE home_info;
INSERT INTO home_info (name, subtitle, description, location, email, availability, profile_image, cv_link) VALUES
('Afifa Sultana',
 'CSE undergraduate at KUET',
 'Computer Science undergraduate at KUET building production-shaped software — Spring Boot services with JWT auth and role-based access, Laravel and PHP web apps, Flutter and Android clients, and a compiler written from scratch in C. I care about the parts users never see: schema design, migrations, auth boundaries, and clean deploys.',
 'Khulna, Bangladesh',
 'afifasultana637@gmail.com',
 'Open to internships & junior roles',
 'assets/images/profile1.jpg',
 'download_cv.php');


-- ----------------------------------------------------------- home_roles ---

TRUNCATE TABLE home_roles;
INSERT INTO home_roles (role, order_no) VALUES
('Software Engineer',     1),
('Backend Developer',     2),
('Full-Stack Developer',  3),
('Spring Boot & PHP',     4);


-- --------------------------------------------------------- home_socials ---

TRUNCATE TABLE home_socials;
INSERT INTO home_socials (platform, url, icon_class, order_no) VALUES
('GitHub',   'https://github.com/Afifa637',                          'fab fa-github',     1),
('LinkedIn', 'https://www.linkedin.com/in/afifa-sultana-346a13256/', 'fab fa-linkedin-in', 2),
('Email',    'mailto:afifasultana637@gmail.com',                     'fas fa-envelope',   3);


-- ---------------------------------------------------------------- about ---

TRUNCATE TABLE about;
INSERT INTO about (short_intro, long_intro, profile_image) VALUES
('I am a third-year Computer Science and Engineering student at Khulna University of Engineering & Technology, and I spend most of my time building things that have a backend.',
 'My work has gravitated toward server-side engineering. I have built Spring Boot services with JWT-secured REST APIs, role-based dashboards, PostgreSQL persistence through JPA, and Liquibase migrations running in Docker — the kind of setup where getting the schema and the auth boundaries right matters more than the UI.

I also like going lower. I wrote a working mini-compiler in C for a small language, built a multi-agent AI game in Godot to compare pathfinding and decision strategies against each other, and put together a solar tracker in C++. Those projects taught me more about how systems actually behave than any tutorial did.

On the product side I ship full-stack: Laravel and PHP applications, a React ration-distribution system deployed on Vercel, Flutter and Android clients backed by Firebase. I am looking for internships or junior roles where I can work on real backend systems with people who will review my code honestly.',
 'assets/images/profile1.jpg');


-- ------------------------------------------------------------ education ---

TRUNCATE TABLE education;
INSERT INTO education (degree, major, institution, location, grade, start_year, end_year, description, order_no) VALUES
('B.Sc. in Computer Science and Engineering', 'Science', 'Khulna University of Engineering & Technology', 'Khulna, Bangladesh', 'CGPA 3.69', '2023', 'Present',
 'Coursework across data structures, algorithms, database systems, compiler design, operating systems, software engineering, and artificial intelligence.', 1),
('Higher Secondary School Certificate', 'Science', 'Viqarunnisa Noon School and College', 'Dhaka, Bangladesh', 'GPA 5.00 / 5.00', '2019', '2021',
 'Science group, with mathematics and physics as the primary focus.', 2),
('Secondary School Certificate', 'Science', 'Viqarunnisa Noon School and College', 'Dhaka, Bangladesh', 'GPA 5.00 / 5.00', '2017', '2019',
 '', 3);


-- --------------------------------------------------------------- skills ---

TRUNCATE TABLE skills;
INSERT INTO skills (skill_name, category, description, order_no) VALUES
('Java',                  'Languages', 'Primary language for Spring Boot services', 1),
('PHP',                   'Languages', 'Laravel and plain PHP applications',        2),
('C++',                   'Languages', 'Embedded control and algorithmic work',     3),
('C',                     'Languages', 'Compiler construction',                     4),
('Python',                'Languages', 'Scripting and automation',                  5),
('JavaScript',            'Languages', 'Browser and Node applications',             6),
('TypeScript',            'Languages', 'Typed front-end projects',                  7),
('Dart',                  'Languages', 'Flutter applications',                      8),
('Swift',                 'Languages', 'iOS coursework',                            9),
('GDScript',              'Languages', 'Godot game and AI work',                   10),
('SQL',                   'Languages', 'Relational querying and schema work',      11),
('Spring Boot 3',         'Backend',   'REST services and server-rendered pages',  12),
('Spring Security',       'Backend',   'Authentication and role-based access',     13),
('Laravel',               'Backend',   'PHP application framework',                14),
('REST APIs',             'Backend',   'API design and documentation',             15),
('JWT Auth',              'Backend',   'Stateless API authentication',             16),
('JPA / Hibernate',       'Backend',   'Object-relational persistence',            17),
('Thymeleaf',             'Backend',   'Server-side templating',                   18),
('Swagger / OpenAPI',     'Backend',   'Generated API documentation',              19),
('HTML5',                 'Frontend',  'Semantic, accessible markup',              20),
('CSS3',                  'Frontend',  'Responsive layout and design systems',     21),
('React',                 'Frontend',  'Component-based interfaces',               22),
('Blade',                 'Frontend',  'Laravel templating',                       23),
('Responsive Design',     'Frontend',  'Mobile-first layouts',                     24),
('Accessibility',         'Frontend',  'Keyboard and screen-reader support',       25),
('PostgreSQL',            'Data',      'Primary relational database',              26),
('MySQL',                 'Data',      'Relational database',                      27),
('Firebase Firestore',    'Data',      'Document store for mobile apps',           28),
('Liquibase',             'Data',      'Versioned schema migrations',              29),
('Schema Design',         'Data',      'Normalisation and indexing',               30),
('Flutter',               'Mobile',    'Cross-platform applications',              31),
('Android (Java/Kotlin)', 'Mobile',    'Native Android development',               32),
('SwiftUI',               'Mobile',    'Declarative iOS interfaces',               33),
('Firebase Auth',         'Mobile',    'Managed authentication',                   34),
('Git',                   'Tooling',   'Version control',                          35),
('GitHub',                'Tooling',   'Collaboration and CI',                     36),
('Docker',                'Tooling',   'Containerised local and deploy setups',    37),
('Docker Compose',        'Tooling',   'Multi-service orchestration',              38),
('Maven',                 'Tooling',   'Java build and dependency management',     39),
('Composer',              'Tooling',   'PHP dependency management',                40),
('Linux',                 'Tooling',   'Server environment',                       41),
('Postman',               'Tooling',   'API testing',                              42);


-- ------------------------------------------------------------- projects ---

TRUNCATE TABLE projects;
INSERT INTO projects (title, category, image, description, details_title, skills_used, role, view_link, repo_name, featured, order_no) VALUES
('Timeless', 'backend', '',
 'A Spring Boot 3 marketplace for reselling luxury watches, with server-rendered pages for buyers and JWT-secured REST APIs for everything else.',
 'Timeless — luxury watch resale marketplace',
 'Java, Spring Boot 3, Spring Security, JWT, Thymeleaf, PostgreSQL, JPA, Liquibase, Docker',
 'Sole developer', '', 'TimeLess_watch_marketplace', 1, 1),

('Amar Ration', 'fullstack', '',
 'A web system for tracking ration beneficiaries, stock levels, and distribution records, so that who received what is a query rather than a paper trail.',
 'Amar Ration — ration distribution management',
 'JavaScript, React, Node.js, REST APIs, Vercel',
 'Sole developer', 'https://amar-ration.vercel.app', 'Amar-Ration', 1, 2),

('SocialStory', 'systems', '',
 'A mini-compiler for SocialScript, a small purpose-built language — lexer, parser, and code generation, implemented from scratch in C.',
 'SocialStory — a compiler written in C',
 'C, Lex / Flex, Yacc / Bison, Compiler Design',
 'Developer', '', 'SocialStory--A-Compiler-Project', 1, 3),

('Prison Break', 'ai', '',
 'A prison-escape game in Godot where several AI techniques compete at the same problem, so their behaviour can be compared directly in one environment.',
 'Prison Break — multi-agent AI competition',
 'GDScript, Godot Engine, Pathfinding, Multi-Agent Systems, Game AI',
 'Developer', '', 'Prison_break', 1, 4),

('Student Management', 'backend', '',
 'A Spring Boot service for student records with role-based access control, versioned migrations, containerised setup, and documented endpoints.',
 'Student Management — Spring Boot service with RBAC',
 'Java, Spring Boot, Spring Security, PostgreSQL, Liquibase, Docker, Swagger',
 'Sole developer', '', 'Student_Management1', 0, 5),

('Heritage Explorer', 'fullstack', 'assets/images/heritage-explorer.jpg',
 'A PHP and MySQL application cataloguing cultural heritage sites, built around a normalised relational schema with search across sites, periods, and regions.',
 'Heritage Explorer — cultural heritage database',
 'PHP, MySQL, Relational Design, JavaScript',
 'Developer', '', 'Heritage-Database-Project', 0, 6),

('AgroConnect', 'fullstack', 'assets/images/agroconnect1.jpg',
 'A Laravel application linking farmers directly with buyers, cutting the intermediary out of small agricultural transactions.',
 'AgroConnect — farmer to buyer platform',
 'PHP, Laravel, Blade, MySQL, Eloquent ORM',
 'Developer', '', 'AgroConnect01', 0, 7),

('Firefly Catcher', 'frontend', 'assets/images/firefly-catcher.jpg',
 'A browser game in TypeScript built around animation timing and responsive input handling.',
 'Firefly Catcher — TypeScript interactive game',
 'TypeScript, HTML Canvas, Game Loop, CSS',
 'Developer', '', 'FireflyCatcher', 0, 8),

('GreenGrocer', 'backend', 'assets/images/green-grocer.jpg',
 'A Java application for grocery inventory and order management, built around object-oriented design and a persistent data layer.',
 'GreenGrocer — Java grocery management',
 'Java, OOP, JDBC, MySQL',
 'Developer', '', 'GreenGrocer', 0, 9),

('Firebase Notes', 'mobile', '',
 'A Swift application using Firebase Authentication and Firestore, where every note is scoped to the user who created it.',
 'Firebase Notes — iOS notes with per-user access',
 'Swift, Firebase Auth, Cloud Firestore, iOS',
 'Developer', '', '2107087_MC_Assignment', 0, 10),

('Social Media App', 'mobile', 'assets/images/social-media.jpg',
 'A cross-platform social application in Flutter covering feeds, profiles, and interactions from one codebase.',
 'Social Media App — Flutter client',
 'Dart, Flutter, Firebase, Material Design',
 'Developer', '', 'Social_media', 0, 11),

('Solar Tracker', 'systems', '',
 'A C++ embedded controller that orients a solar panel toward the strongest light source using sensor feedback and servo control.',
 'Solar Tracker — embedded sun-tracking controller',
 'C++, Microcontroller, Sensors, Servo Control, Embedded Systems',
 'Developer', '', 'SolarTracker', 0, 12);


-- --------------------------------------------------------- contact_info ---

TRUNCATE TABLE contact_info;
INSERT INTO contact_info (type, icon, data, input_type, label, value, order_no) VALUES
('Email',    'fas fa-envelope',    'afifasultana637@gmail.com', 'card', NULL, NULL, 1),
('GitHub',   'fab fa-github',      'Afifa637',                  'card', NULL, NULL, 2),
('LinkedIn', 'fab fa-linkedin-in', 'Afifa Sultana',             'card', NULL, NULL, 3),
('Location', 'fas fa-location-dot','Khulna, Bangladesh',        'card', NULL, NULL, 4),
(NULL, NULL, NULL, 'radio', 'Job opportunity',   'Job opportunity',   5),
(NULL, NULL, NULL, 'radio', 'Freelance project', 'Freelance project', 6),
(NULL, NULL, NULL, 'radio', 'Collaboration',     'Collaboration',     7),
(NULL, NULL, NULL, 'radio', 'Something else',    'Something else',    8);


-- --------------------------------------------------------------- footer ---

TRUNCATE TABLE footer;
INSERT INTO footer (social_name, social_icon, social_link, footer_text, order_no) VALUES
('GitHub',   'fab fa-github',      'https://github.com/Afifa637',                          NULL, 1),
('LinkedIn', 'fab fa-linkedin-in', 'https://www.linkedin.com/in/afifa-sultana-346a13256/', NULL, 2),
('Email',    'fas fa-envelope',    'mailto:afifasultana637@gmail.com',                     NULL, 3),
(NULL, NULL, NULL, '© 2026 Afifa Sultana. All rights reserved.', 4);


-- --------------------------------------------------------------- admins ---
--
-- No account is seeded, because a shipped default password is a live
-- vulnerability the moment the site is deployed.
--
-- Generate a hash, then insert it:
--
--   php -r "echo password_hash('your-strong-password', PASSWORD_BCRYPT), PHP_EOL;"
--
--   INSERT INTO admins (name, email, password)
--   VALUES ('Afifa Sultana', 'afifasultana637@gmail.com', '<paste-the-hash-here>');
--
-- Or use the helper, which prompts and writes the row for you:
--
--   php database/create_admin.php
