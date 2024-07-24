

![install video](prasso_installation.mp4)
![fundamentals overview](https://youtu.be/bBe3Qu7fxeY)


mermaid diagram info

erDiagram

    users {
        id int
        name string
        email string
        password string
        two_factor_secret string
        two_factor_recovery_codes string
        remember_token string
        current_team_id int
        profile_photo_path string
        firebase_uid string
        pn_token string
        stripe_id string
        pm_type string
        pm_last_four string
        enableMealReminders int
        reminderTimesJson string
        timeZone string
        phone string
        version string
    }

    teams {
        id int
        user_id int
        name string
        personal_team int
        phone string
        parent_id int
    }

     sites {
        id int
        site_name string
        host string
        main_color string
        logo_image string
        database string
        app_specific_js string
        app_specific_css string
        image_folder string
        favicon string
        description string
        supports_registration int
        subteams_enabled int
    }


    team_user {
        id int
        team_id int
        user_id int
        role string
    }

   
    team_site {
        id int
        site_id int
        team_id int
    }

    apps {
        id int
        token_id int
        team_id int
        site_id int
        appicon string
        app_name string
        page_title string
        page_url string
        main_color string
        sort_order int
    }

    tabs {
        id int
        app_id int
        team_role int
        icon string
        label string
        page_title string
        page_url string
        sort_order int
        request_header string
        parent int
        restrict_role int
    }

    
    users ||--o{ teams : "user_id"
    teams ||--o{ team_user : "team_id"
    users ||--o{ team_user : "user_id"
    teams ||--o{ team_site : "team_id"
    teams ||--o{ teams : "parent_id"
    sites ||--o{ team_site : "site_id"
    teams ||--o{ apps : "team_id"
    sites ||--o{ apps : "site_id"
    apps ||--o{ tabs : "app_id"




erDiagram
     sites {
        site_name string
        host string
    }
    site_pages {
        section
        title
        description
        url
    }
    site_page_templates {
        template_name
        title
    }
    site_page_data {
        data_key
        json_data
    }
    sites ||--o{ site_pages : "sites have site pages"
    site_pages || --o{ site_page_templates : "site_pages can be made of templates"






erDiagram

  
    apps {
        team_id int
        site_id int
        app_name string
    }

    tabs {
        app_id int
        team_role int
        page_title string
        page_url string
    }

    

    apps || --o{ tabs : "app views are made of defined tabs"

