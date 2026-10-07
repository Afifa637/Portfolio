<?php

/**
 * Declarative admin resources.
 *
 * Each entry describes a table well enough for admin/resource.php to render a
 * full list / create / edit / delete / reorder screen for it. Ten near-identical
 * CRUD files were the alternative; this keeps the behaviour consistent and makes
 * adding a new editable section a matter of describing it here.
 *
 * Field types map to the controls in admin_field(): text, textarea, select,
 * bool, image, list, email, url, number.
 */

declare(strict_types=1);

/** Icon names available to the inline icon set, for the select controls. */
function admin_icon_options(): array
{
    $names = [
        'code', 'server', 'database', 'layout', 'layers', 'smartphone', 'settings',
        'terminal', 'cpu', 'award', 'star', 'graduation', 'briefcase', 'calendar',
        'book', 'zap', 'activity', 'mail', 'phone', 'map-pin', 'github', 'linkedin',
        'facebook', 'skype', 'twitter', 'globe', 'external', 'check', 'send', 'copy',
    ];

    return array_combine($names, $names);
}

/** @return array<string, array<string, mixed>> */
function admin_resources(): array
{
    $categories = [];

    if (Database::hasTable('project_categories')) {
        foreach (Database::all('SELECT slug, label FROM project_categories ORDER BY order_no, id') as $row) {
            $categories[(string) $row['slug']] = (string) $row['label'];
        }
    }

    $skillGroups = [];

    if (Database::hasTable('skill_groups')) {
        foreach (Database::all('SELECT id, name FROM skill_groups ORDER BY order_no, id') as $row) {
            $skillGroups[(string) $row['id']] = (string) $row['name'];
        }
    }

    return [
        'skill_groups' => [
            'title'    => 'Skill groups',
            'singular' => 'skill group',
            'blurb'    => 'The cards in the Stack section. Each group holds a list of technologies.',
            'order'    => 'order_no, id',
            'columns'  => ['name' => 'Group', 'note' => 'Caption'],
            'fields'   => [
                ['name' => 'name',  'label' => 'Group name', 'required' => true, 'maxlength' => 80],
                ['name' => 'icon',  'label' => 'Icon', 'type' => 'select', 'options' => admin_icon_options(), 'default' => 'code'],
                ['name' => 'note',  'label' => 'Caption', 'hint' => 'Small line under the group name', 'maxlength' => 190],
            ],
            // Skills point at a group by id. Clearing the reference keeps them
            // on the site under "Also" rather than leaving a dangling id.
            'on_delete' => static function (int $id): void {
                Database::execute(
                    'UPDATE skills SET group_id = NULL, category = NULL WHERE group_id = ?',
                    [$id]
                );
            },
        ],

        'skills' => [
            'title'    => 'Skills',
            'singular' => 'skill',
            'blurb'    => 'Individual technologies. Assign each to a group so it appears on the site.',
            'order'    => 'group_id, order_no, id',
            'columns'  => ['skill_name' => 'Skill', 'category' => 'Group'],
            'fields'   => [
                ['name' => 'skill_name', 'label' => 'Skill', 'required' => true, 'maxlength' => 120],
                ['name' => 'group_id', 'label' => 'Group', 'type' => 'select', 'options' => $skillGroups,
                 'hint' => 'Skills without a group are listed under "Also".'],
                ['name' => 'description', 'label' => 'Note', 'hint' => 'Optional, not shown on the site', 'maxlength' => 255],
            ],
            // Keep the legacy text column aligned with the chosen group.
            'before_save' => static function (array $data, int $id = 0): array {
                $groupId = (int) ($data['group_id'] ?? 0);

                $data['category'] = $groupId > 0
                    ? (string) (Database::first('SELECT name FROM skill_groups WHERE id = ?', [$groupId])['name'] ?? '')
                    : '';

                return $data;
            },
        ],

        'education' => [
            'title'    => 'Education',
            'singular' => 'qualification',
            'blurb'    => 'Shown as a timeline. Leave the end year blank to mark it current.',
            'order'    => 'order_no, start_year DESC, id',
            'columns'  => ['degree' => 'Qualification', 'institution' => 'Institution', 'grade' => 'Grade'],
            'fields'   => [
                ['name' => 'degree',      'label' => 'Qualification', 'required' => true, 'maxlength' => 190],
                ['name' => 'institution', 'label' => 'Institution',   'required' => true, 'maxlength' => 190],
                ['name' => 'location',    'label' => 'Location',      'maxlength' => 160],
                ['name' => 'start_year',  'label' => 'Start year',    'maxlength' => 16, 'placeholder' => '2023'],
                ['name' => 'end_year',    'label' => 'End year',      'maxlength' => 16,
                 'hint' => 'Leave blank or write "Present" to show a Current badge', 'placeholder' => 'Present'],
                ['name' => 'grade',       'label' => 'Grade',         'maxlength' => 80, 'placeholder' => 'CGPA 3.69'],
                ['name' => 'major',       'label' => 'Badge text',    'maxlength' => 190, 'placeholder' => 'Science'],
                ['name' => 'description', 'label' => 'Detail',        'type' => 'textarea', 'rows' => 3],
            ],
        ],

        'experience' => [
            'title'    => 'Experience',
            'singular' => 'role',
            'blurb'    => 'Jobs, internships, freelance work and collaborations.',
            'order'    => 'is_current DESC, order_no, id',
            'columns'  => ['title' => 'Role', 'company' => 'Organisation', 'end_date' => 'Until'],
            'fields'   => [
                ['name' => 'title',       'label' => 'Role',         'required' => true, 'maxlength' => 190],
                ['name' => 'company',     'label' => 'Organisation', 'maxlength' => 190],
                ['name' => 'location',    'label' => 'Location',     'maxlength' => 160],
                ['name' => 'start_date',  'label' => 'From',         'maxlength' => 50, 'placeholder' => 'Jan 2026'],
                ['name' => 'end_date',    'label' => 'Until',        'maxlength' => 50, 'placeholder' => 'Present'],
                ['name' => 'is_current',  'label' => 'Current role', 'type' => 'bool', 'on_label' => 'Currently here'],
                ['name' => 'description', 'label' => 'What you did', 'type' => 'textarea', 'rows' => 4],
                ['name' => 'tech_stack',  'label' => 'Technologies', 'type' => 'list',
                 'hint' => 'One per line', 'rows' => 4],
            ],
        ],

        'activities' => [
            'title'    => 'Activities',
            'singular' => 'activity',
            'blurb'    => 'Clubs, societies, competitions and volunteering.',
            'order'    => 'order_no, id',
            'columns'  => ['role' => 'Role', 'org' => 'Organisation'],
            'fields'   => [
                ['name' => 'role',   'label' => 'Your role', 'required' => true, 'maxlength' => 120, 'placeholder' => 'Member'],
                ['name' => 'org',    'label' => 'Organisation', 'required' => true, 'maxlength' => 255],
                ['name' => 'icon',   'label' => 'Icon', 'type' => 'select', 'options' => admin_icon_options(), 'default' => 'award'],
                ['name' => 'detail', 'label' => 'Detail', 'type' => 'textarea', 'rows' => 3],
            ],
        ],

        'services' => [
            'title'    => 'Services',
            'singular' => 'service',
            'blurb'    => 'The "What I can build for you" cards.',
            'order'    => 'order_no, id',
            'columns'  => ['title' => 'Service'],
            'fields'   => [
                ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190],
                ['name' => 'icon',  'label' => 'Icon', 'type' => 'select', 'options' => admin_icon_options(), 'default' => 'server'],
                ['name' => 'body',  'label' => 'Description', 'type' => 'textarea', 'rows' => 4, 'required' => true],
            ],
        ],

        'about_facts' => [
            'title'    => 'About facts',
            'singular' => 'fact',
            'blurb'    => 'The label/value list beside your portrait.',
            'order'    => 'order_no, id',
            'columns'  => ['label' => 'Label', 'value' => 'Value'],
            'fields'   => [
                ['name' => 'label', 'label' => 'Label', 'required' => true, 'maxlength' => 120, 'placeholder' => 'Based in'],
                ['name' => 'value', 'label' => 'Value', 'required' => true, 'maxlength' => 255, 'placeholder' => 'Khulna, Bangladesh'],
            ],
        ],

        'home_roles' => [
            'title'    => 'Hero roles',
            'singular' => 'role',
            'blurb'    => 'Cycled by the typing effect under your name.',
            'order'    => 'order_no, id',
            'columns'  => ['role' => 'Role'],
            'fields'   => [
                ['name' => 'role', 'label' => 'Role', 'required' => true, 'maxlength' => 160],
            ],
        ],

        'home_socials' => [
            'title'    => 'Social links',
            'singular' => 'link',
            'blurb'    => 'Shown in the hero, the mobile menu and the footer.',
            'order'    => 'order_no, id',
            'columns'  => ['platform' => 'Platform', 'url' => 'URL'],
            'fields'   => [
                ['name' => 'platform',   'label' => 'Platform', 'required' => true, 'maxlength' => 80],
                ['name' => 'url',        'label' => 'URL', 'type' => 'url', 'required' => true, 'maxlength' => 255],
                ['name' => 'icon_class', 'label' => 'Icon', 'type' => 'select', 'options' => admin_icon_options(),
                 'hint' => 'Matched to the platform name when left unset'],
            ],
        ],

        'contact_channels' => [
            'title'    => 'Contact info',
            'singular' => 'channel',
            'blurb'    => 'The cards beside the contact form.',
            'order'    => 'order_no, id',
            'columns'  => ['label' => 'Label', 'value' => 'Value'],
            'fields'   => [
                ['name' => 'label', 'label' => 'Label', 'required' => true, 'maxlength' => 120],
                ['name' => 'value', 'label' => 'Value', 'required' => true, 'maxlength' => 255],
                ['name' => 'href',  'label' => 'Link',  'maxlength' => 255,
                 'hint' => 'Leave blank to show plain text. Use mailto: for an address.'],
                ['name' => 'icon',  'label' => 'Icon', 'type' => 'select', 'options' => admin_icon_options(), 'default' => 'mail'],
            ],
        ],

        'contact_purposes' => [
            'title'    => 'Enquiry types',
            'singular' => 'option',
            'blurb'    => 'Options in the "What is this about?" dropdown.',
            'order'    => 'order_no, id',
            'columns'  => ['label' => 'Option'],
            'fields'   => [
                ['name' => 'label', 'label' => 'Option', 'required' => true, 'maxlength' => 120],
            ],
        ],

        'project_categories' => [
            'title'    => 'Project categories',
            'singular' => 'category',
            'blurb'    => 'The filter buttons above the project grid.',
            'order'    => 'order_no, id',
            'columns'  => ['label' => 'Label', 'slug' => 'Slug'],
            'fields'   => [
                ['name' => 'label', 'label' => 'Label', 'required' => true, 'maxlength' => 80],
                ['name' => 'slug',  'label' => 'Slug', 'required' => true, 'maxlength' => 40,
                 'hint' => 'Lowercase, no spaces. Projects using this category follow a rename automatically.'],
            ],
            // A rename has to carry its projects with it, or they silently drop
            // out of every filter.
            'before_save' => static function (array $data, int $id): array {
                $data['slug'] = trim(strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $data['slug'])), '-');

                if ($id > 0) {
                    $previous = Database::first('SELECT slug FROM project_categories WHERE id = ?', [$id]);

                    if ($previous && $previous['slug'] !== $data['slug']) {
                        Database::execute(
                            'UPDATE projects SET category = ? WHERE category = ?',
                            [$data['slug'], $previous['slug']]
                        );
                    }
                }

                return $data;
            },
            // Refuse rather than quietly stranding projects in a category that
            // no longer has a filter button.
            'on_delete' => static function (int $id): ?string {
                $row = Database::first('SELECT slug, label FROM project_categories WHERE id = ?', [$id]);

                if (!$row) {
                    return null;
                }

                $count = (int) (Database::first(
                    'SELECT COUNT(*) AS n FROM projects WHERE category = ?',
                    [$row['slug']]
                )['n'] ?? 0);

                if ($count > 0) {
                    return sprintf(
                        '%d project%s still use "%s". Move them to another category first.',
                        $count,
                        $count === 1 ? '' : 's',
                        $row['label']
                    );
                }

                return null;
            },
        ],
    ];
}
