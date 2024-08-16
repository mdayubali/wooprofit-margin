<div class="wrap">
    <form action="">
<!--    Switcher -->
        <div class="switch-container">
            <label class="switch-label">Active Rule</label>
            <div class="switch-wrapper">
                <div class="switch">
                    <input type="checkbox" id="toggle-switch">
                    <label for="toggle-switch" class="slider"></label>
                </div>
                <span class="switch-info"><small>Select to enable or disable this discount rule</small></span>
            </div>
        </div>


<!--Discount role -->
        <div class="discount-rules-container">
            <label class="discount-rules-label">Discount Rules</label>

                <div class="discount-rules-inputs">
                    <div class="input-group">
                        <label for="from-field">From</label>
                        <input type="text" id="from-field" placeholder="1" class="from-field">
                    </div>
                    <div class="input-group">
                        <label for="to-field">To</label>
                        <input type="text" id="to-field" placeholder="" class="to-field">
                    </div>
                    <div class="input-group">
                        <label for="apply-field">Apply</label>
                        <select id="apply-field" class="rule-options">
                            <option value="percentage">% Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <input type="text" id="value-field" placeholder="20" class="value-field"> %
                    </div>
                </div>

        </div>


<!--        Apply discount to -->
        <div class="discount-application-container">
            <label class="discount-application-label">Apply discount to:</label>
            <div class="p-relative">
                <div class="discount-application-options">
                    <div class="radio-group">
                        <input type="radio" id="all-users" name="discount-application" value="all">
                        <label for="all-users">All users</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="specific-user" name="discount-application" value="specific">
                        <label for="specific-user">Only to a specific user</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="user-roles" name="discount-application" value="roles">
                        <label for="user-roles">Only to specific user roles</label>
                    </div>
                </div>
                <span class="application-info"><small>Choose to apply the rule to all users or only specific user roles</small></span>
            </div>
        </div>


        <!--    Switcher for product exclude-->


        <div class="switch-container excluded-products">
            <label class="switch-label">Exclude Products</label>
            <div class="switch">
                <input type="checkbox" id="exclude-toggle-switch">
                <label for="exclude-toggle-switch" class="slider"></label>
            </div>
            <span class="switch-description">
    <small>Enable if you want to exclude specific products from this rule</small>
  </span>
        </div>

 <button>Save</button>



    </form>
</div>
