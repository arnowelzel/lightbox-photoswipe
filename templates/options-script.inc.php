<script>
    function lbwpsUpdateDescriptionCheck(checkbox)
    {
        let useDescription = document.getElementById("lightbox_photoswipe_usepostdata");
        if (checkbox.checked) {
            useDescription.disabled = false;
        } else {
            useDescription.disabled = true;
        }
    }

    function lbwpsUpdateExifDateCheck(checkbox)
    {
        let showExifDate = document.getElementById("lightbox_photoswipe_showexif_date");
        if (checkbox.checked) {
            showExifDate.disabled = false;
        } else {
            showExifDate.disabled = true;
        }
    }

    function lbwpsSwitchTab(tab)
    {
        let num=1;
        while (num < 8) {
            if (tab == num) {
                document.getElementById('lbwps-switch-'+num).classList.add('nav-tab-active');
                document.getElementById('lbwps-tab-'+num).style.display = 'block';
            } else {
                document.getElementById('lbwps-switch-'+num).classList.remove('nav-tab-active');
                document.getElementById('lbwps-tab-'+num).style.display = 'none';
            }
            num++;
        }
        document.getElementById('lbwps-switch-'+tab).blur();
        if (tab == 1 && ("pushState" in history)) {
            history.pushState("", document.title, window.location.pathname+window.location.search);
        } else {
            location.hash = 'tab-' + tab;
        }
        let referrer = document.getElementsByName('_wp_http_referer');
        if (referrer[0]) {
            let parts = referrer[0].value.split('#');
            if (tab>1) {
                referrer[0].value = parts[0] + '#tab-' + tab;
            } else {
                referrer[0].value = parts[0];
            }
        }
    }

    function lbwpsUpdateCurrentTab()
    {
        if(location.hash == '') {
            lbwpsSwitchTab(1);
        } else {
            let num = 1;
            while (num < 8) {
                if (location.hash == '#tab-' + num) lbwpsSwitchTab(num);
                num++;
            }
        }
    }

    function lbwpsUpdateVersion()
    {
        let optionsV4 = document.getElementsByClassName('lbwps-ver4');
        let optionsV5 = document.getElementsByClassName('lbwps-ver5');
        if (document.getElementById('lightbox_photoswipe_version-0').checked) {
            for (let i=0; i < optionsV4.length; i++) {
                optionsV4.item(i).style.display = '';
            }
            for (let i=0; i < optionsV5.length; i++) {
                optionsV5.item(i).style.display = 'none';
            }
        } else {
            for (let i=0; i < optionsV5.length; i++) {
                optionsV5.item(i).style.display = ''
            }
            for (let i=0; i < optionsV4.length; i++) {
                optionsV4.item(i).style.display = 'none';
            }
        }
    }

    lbwpsUpdateDescriptionCheck(document.getElementById("lightbox_photoswipe_show_caption"));
    lbwpsUpdateExifDateCheck(document.getElementById("lightbox_photoswipe_showexif"));
    lbwpsUpdateCurrentTab();
    lbwpsUpdateVersion();

    window.addEventListener('popstate', (event) => {
        lbwpsUpdateCurrentTab();
    });
    document.getElementById('lightbox_photoswipe_show_caption').addEventListener('click', (event) => {
        lbwpsUpdateDescriptionCheck(event.target);
    });
    document.getElementById('lightbox_photoswipe_showexif').addEventListener('click', (event) => {
        lbwpsUpdateExifDateCheck(event.target)
    });
    document.getElementById('lightbox_photoswipe_version-0').addEventListener('click', (event) => {
        lbwpsUpdateVersion()
    });
    document.getElementById('lightbox_photoswipe_version-1').addEventListener('click', (event) => {
        lbwpsUpdateVersion()
    });
</script>
