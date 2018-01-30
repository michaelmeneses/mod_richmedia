<div class="loading"></div>
<div id="richmedia">
    <div id="richmedia-fullcontent">
        <div id="head">
            <img id="richmedia-logo"/>
            <span id="richmedia-title"></span>
        </div>	
        <div id="richmedia-content">
            <div id="richmedia-content-container">
                <div id="left">
                    <section id="cuePlayer">

                        <?php
                        if (!$audioMode) {
                            ?>
                            <video id="video" preload="auto" onpause="Player.pauseVideo()" onplay="Player.playVideo()"></video>
                            <?php
                        } else {
                            ?>
                            <audio id="video" preload="auto" onpause="Player.pauseVideo()" onplay="Player.playVideo()"></audio>
                            <?php
                        }
                        ?>

                    </section>
                    <div id="text">
                        <p id="presentername"></p>
                        <p id="presenterbio"></p>
                    </div>	
                </div>	
                <div id="subtitles"></div>
                <div class="srt" data-video="video" />
            </div>
        </div>
    </div>	
    <!-- barre de controle -->
    <div id="controles">
        <div id="progress-bar">
            <div id="progress"></div>
        </div>
        <div id="controles-icons">
            <div id="controles-left">
                <input id="list" type="button" class="richmedia-icon" />
                <input type="button" id="prev" class="richmedia-icon" />
                <input type="button" id="playbutton" class="richmedia-icon"/>
                <input type="button" id="next" class="richmedia-icon"/>
                <input type="button" id="srt" class="richmedia-icon"/>
            </div>
            <div id="controles-right">
                
                <?php
                if (!$audioMode) {
                ?>
                <input type="button" id="thumbnail" class="richmedia-icon"/>

                <input type="button" id="closed" class="richmedia-icon" />
                <select id="selectview" disabled="disabled">
                    <option value="#" selected="selected" disabled="disabled"></option>
                    <option value="1"></option>
                    <option value="2"></option>
                    <option value="3"></option>
                </select>
                <?php
                }
                ?>
                <input type="range" id="volume-bar" min="0" max="1" step="0.1" value="1">
                <input type="button" id="fullscreen" class="richmedia-icon" />

            </div>
        </div>
    </div>
    <div id="richmedia-summary">
        <table>
            <thead></thead>
            <tbody></tbody>
        </table>
    </div>
</div>