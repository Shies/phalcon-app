<form action="/users/login" method="post">

    <h2>Login Demo</h2>

    <fieldset>

        <div class="control-group">
            <label for="name" class="control-label">username：</label>
            <div class="controls">
                <input type="text" id="name" name="name" />
            </div>
        </div>

        <div class="control-group">
            <label for="pass" class="control-label">password：</label>
            <div class="controls">
                <input type="password" id="pass" name="pass" />
            </div>
        </div>


        <div class="control-group">
            <input type="submit" value="login" class="btn btn-primary" />
        </div>

        <!--
        <div class="control-group">
            <label for="profilesId" class="control-label">profilesId</label>
            <div class="controls">
                <select id="profilesId" name="profilesId">
                    <option value="">...</option>
                    <option value="1">Vegetables</option>
                    <option value="2">Fruits</option>
                </select>
            </div>
        </div>
        -->

        <!--
        <div class="control-group">
            <label for="price" class="control-label">Price</label>
            <div class="controls"><input type="text" id="price" name="price" /></div>
        </div>
        -->

    </fieldset>

</form>