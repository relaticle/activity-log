export default defineAppConfig({
    docus: {
        title: 'Activity Log',
        description: 'Unified chronological timeline for any Eloquent model, powered by spatie/laravel-activitylog and Filament v5.',
        header: {
            logo: {
                alt: 'Activity Log Logo',
            }
        }
    },
    seo: {
        title: 'Activity Log',
        description: 'Unified chronological timeline for any Eloquent model, powered by spatie/laravel-activitylog and Filament v5.',
    },
    github: {
        repo: 'activity-log',
        owner: 'Relaticle',
        edit: true,
        rootDir: 'docs'
    },
    socials: {},
    ui: {
        colors: {
            primary: 'violet',
            neutral: 'zinc'
        }
    },
    uiPro: {
        pageHero: {
            slots: {
                container: 'flex flex-col lg:grid py-16 sm:py-20 lg:py-24 gap-16 sm:gap-y-2'
            }
        }
    },
    toc: {
        title: 'On this page',
        bottom: {
            title: 'Ecosystem',
            edit: 'https://github.com/Relaticle/activity-log',
            links: [
                {
                    icon: 'i-lucide-kanban',
                    label: 'Flowforge',
                    to: 'https://relaticle.github.io/flowforge',
                    target: '_blank'
                },
                {
                    icon: 'i-lucide-sliders',
                    label: 'Custom Fields',
                    to: 'https://relaticle.github.io/custom-fields',
                    target: '_blank'
                },
                {
                    icon: 'i-simple-icons-laravel',
                    label: 'FilaForms',
                    to: 'https://filaforms.app',
                    target: '_blank'
                }
            ]
        }
    }
})
