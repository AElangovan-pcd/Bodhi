<div class="modal fade " id="functions_list" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4>Functions <a href="#mathjax"><small>(Jump to MathJax Documentation)</small></a></h4></div>
            <table class="table">
                <tbody>
                <th>LabPal Functions</th><th></th>

                <tr><td><i>Uncertainty Functions</i></td></tr>
                <tr><td>withinRange(value, center, range) </td><td> Returns if the <i>value</i> is in the specified <i>range</i> of the <i>center</i></td></tr>
                <tr><td>withinPercent(value, center, percent) </td><td> Returns if the <i>value</i> is in the specified <i>percent</i> (<i>e.g.</i> 5 for 5%) of the <i>center</i></td></tr>
                <tr><td>sigfigs(variable) </td><td> Gets the number of significant figures reported by a student.  10.245 will return 5.  1.23e-4 will return 3.</td></tr>
                <tr><td>decimal(variable) </td><td> Gets decimal precision of the value. Returns negative values for numbers with trailing zeroes that are not significant. 4.20 return 2, 420 returns -1.</td></tr>
                <tr><td>precision(variable) </td><td> See decimal function for similar, but better behavior. Gets the order of magnitude of the precision reported.  0.042 will return 1e-3.  1e-3 will return 1e-3.  1.0e-3 will return 1e-4.  For values >=1, 0 is returned.</td></tr>
                <tr><td>mantissa(variable) </td><td> Gets the value without the order of magnitude.  Useful for comparing if answers are off by an order of magnitude.  123, 1.23e12, and .00123 will all return 1.23.  Computed as variable/pow(10,(floor(log10(abs(variable))))).</td></tr>

                <tr><td><i>Array Regression Functions</i></td></tr>
                <tr><td>intercept($x$ array variable, $y$ array variable) </td><td> Find the y-intercept given two array variables</td></tr>
                <tr><td>residual($x$ array variable, $y$ array variable) </td><td> The $R^2$ value of a line given two array variables</td></tr>
                <tr><td>slope($x$ array variable, $y$ array variable) </td><td> Find the slope of two array variables</td></tr>
                <tr><td>slope_error($x$ array variable, $y$ array variable) </td><td> Find the standard error of the slope for a linear regression</td></tr>
                <tr><td>intercept_error($x$ array variable, $y$ array variable) </td><td> Find the standard error of the intercept for a linear regression</td></tr>

                <tr><td><i>Array Statistics Functions</i></td></tr>
                <tr><td>count(array variable)</td><td>Determines the number of values in the array variable.</td></tr>
                <tr><td>mean(array variable)</td><td>Determines the mean of the values in the array variable.</td></tr>
                <tr><td>stdev(array variable)</td><td>Determines the standard deviation of the values in the array variable.</td></tr>
                <tr><td>count_true(array variable)</td>Counts the number of true statements exist in an array.  For example, if count_true(array(1=1,2=2,1=2)) will return 2.</tr>
                <tr><td>array_min(array variable)</td><td>Determines the minimum value in the array.</td></tr>
                <tr><td>array_max(array variable)</td><td>Determines the maximum value in the array.</td></tr>

                <tr><td><i>Array Manipulations</i></td></tr>
                <tr><td>array(variable or value, variable or value, ....)</td><td>Builds an intermediate array from a set of values or variables.</td></tr>
                <tr><td>each(array var1, array var2, comparison type, [tolerance])</td><td>Checks whether all values in array var2 match the values in array var1.  Options for comparison type are "exact", "withinPercent", "withinRange".  Note that you must use double quotes on the comparison type. The default value for the tolerance if not supplied is 0.  Example usage: each(arr1,arr2,"withinPercent",5) checks if each value in arr2 is within 5 percent of each value in arr1.</td></tr>
                <tr><td>eachString(array var1, array var2, comparison type, [case-sensitive?])</td><td>Checks whether all strings in array var2 match the values in array var1.  Options for comparison type are "exact", "exactPermute".  Note that you must use double quotes on the comparison type. You can use 0 or 1 to indicate whether the comparison is case-sensitive.  The default value for case-sensitive if not supplied is 0.  "exactPermute" allows the strings to be in any order, so long as all strings match.  Example usage: each(arr1,arr2,"exactPermute")</td></tr>
                <tr><td>element(array variable, index)</td><td>Returns the value in a particular position in an array.  The first index in the array is at index 0.  To access the last value in an array, use element(arr,count(arr)).</td></tr>
                <tr><td>indexOf(array variable, value)</td><td>Returns the array index of a particular value.</td></tr>

                <tr><td><i>Variable Manipulations</i></td></tr>
                <tr><td>concatenate(variable,variable2,...)</td><td> Concatenates strings. To include a space, use <i>concatenate(var1," ",var2)</i>.</td></tr>
                <tr><td>fuzzyMap(variable,range,input1, output1, input2, output2, etc..., [default mapping])</td><td> Returns <i>output</i> based on which <i>input</i> matches the chosen <i>variable</i> within the range <i>range</i>.  Optionally, include a default output at the end of the list (include only the output and no input).</td></tr>
                <tr><td>map(variable,case-sensitive?,input1, output1, input2, output2, etc..., [default mapping])</td><td> Returns <i>output</i> based on which <i>input</i> matches the chosen <i>variable</i>. Use a 0 for case-insensitive and a 1 for case-sensitive.  Inputs may be strings, but must be in quotation marks (eg. "input1") and the chosen variable type must be 'String' or 'Computed.' Optionally, include a default output at the end of the list (include only the output and no input).</td></tr>
                <tr><td>stringCheck(variable,case-sensitive?,string1,string2, etc...) </td><td> Checks whether a string variable matches a list of strings.  Use a 0 for case-insensitive and a 1 for case-sensitive.  Strings must be in quotation marks (e.g. "string1") and the chosen variable type must be 'String'</td></tr>

                <tr><td><i>Chemical Formulas</i></td></tr>
                <tr><td>checkChemical(variable,symbols,charge,phase)</td><td> Checks if all portions of a chemical formula variable match.  Symbols and phases are given as strings with double quotes and charges are given as integers. Phases should not include parentheses.  Example: <i>checkChemical(var1,"Mg",2,"aq")</i>. To use a negative charge enter, for example, -2.</td></tr>
                <tr><td>checkChemicalSymbols(variable,symbols)</td><td> Checks if the molecular formula regardless of charge and phase match.  Symbols are given as strings with double quotes. Example: <i>checkChemicalSymbols(var1,"C2H5O")</i>.</td></tr>
                <tr><td>checkChemicalCharge(variable,charge)</td><td> Checks if the charge matches.  Charges are given as numbers. Example: <i>checkChemicalCharge(var1,-3)</i>.</td></tr>
                <tr><td>checkChemicalPhase(variable,charge)</td><td> Checks if the phase matches.  Phases are given as strings with double quotes and should not include parentheses. Options for phases are "s", "l", "g", "aq", "". Example: <i>checkChemicalPhase(var1,"s")</i>.</td></tr>

                <!--<tr><td>solved(...) </td><td> Returns true/false whether the student has correclty solved the given question number in the assignment</td></tr>-->

                <th>Boolean Logic</th><th></th>
                <tr><td><i>condition1 </i>&&<i> condition2</i></td><td>Requires both condition1 <b>and</b> condition2 to be true.</td></tr>
                <tr><td><i>condition1 </i>||<i> condition2</i></td><td>Requires either condition1 <b>or</b> condition2 to be true.</td></tr>

                <th>Miscelaneous</th><th></th>
                <tr><td>email()</td><td>Gets the user email address. Can be used to lookup assigned values, e.g. using map.</td></tr>

                <th>Basic String Functions</th><th></th>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.strpos.php">strpos(...)</a></td><td>  Find the position of the first occurrence of a substring in a string</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.substr.php">substr(...)</a></td><td>  Return part of a string</td></tr>

                <th>Basic Math Functions</th><th></th>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.abs.php">abs(...)</a></td><td>  Absolute value</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.acos.php">acos(...)</a></td><td>  Arc cosine</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.acosh.php">acosh(...)</a></td><td>  Inverse hyperbolic cosine</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.asin.php">asin(...)</a></td><td>  Arc sine</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.asinh.php">asinh(...)</a></td><td>  Inverse hyperbolic sine</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.atan2.php">atan2(...)</a></td><td>  Arc tangent of two variables</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.atan.php">atan(...)</a></td><td>  Arc tangent</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.atanh.php">atanh(...)</a></td><td>  Inverse hyperbolic tangent</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.base-convert.php">base_convert(...)</a></td><td>  Convert a number between arbitrary bases</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.bindec.php">bindec(...)</a></td><td>  Binary to decimal</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.ceil.php">ceil(...)</a></td><td>  Round fractions up</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.cos.php">cos(...)</a></td><td>  Cosine</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.cosh.php">cosh(...)</a></td><td>  Hyperbolic cosine</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.decbin.php">decbin(...)</a></td><td>  Decimal to binary</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.dechex.php">dechex(...)</a></td><td>  Decimal to hexadecimal</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.decoct.php">decoct(...)</a></td><td>  Decimal to octal</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.deg2rad.php">deg2rad(...)</a></td><td>  Converts the number in degrees to the radian equivalent</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.exp.php">exp(...)</a></td><td>  Calculates the exponent of e</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.expm1.php">expm1(...)</a></td><td>  Returns exp(number) - 1, computed in a way that is accurate even when the value of number is close to zero</td></tr>
                <tr><td>factorial(variable)</td><td>Computes the factorial of the variable.  Accepts positive integers.</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.floor.php">floor(...)</a></td><td>  Round fractions down</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.fmod.php">fmod(...)</a></td><td>  Returns the floating point remainder (modulo) of the divisionof the arguments</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.getrandmax.php">getrandmax(...)</a></td><td>  Show largest possible random value</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.hexdec.php">hexdec(...)</a></td><td>  Hexadecimal to decimal</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.hypot.php">hypot(...)</a></td><td>  Calculate the length of the hypotenuse of a right-angle triangle</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.intdiv.php">intdiv(...)</a></td><td>  Integer division</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.is-finite.php">is_finite(...)</a></td><td>  Finds whether a value is a legal finite number</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.is-infinite.php">is_infinite(...)</a></td><td>  Finds whether a value is infinite</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.is-nan.php">is_nan(...)</a></td><td>  Finds whether a value is not a number</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.lcg-value.php">lcg_value(...)</a></td><td>  Combined linear congruential generator</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.log10.php">log10(...)</a></td><td>  Base-10 logarithm</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.log1p.php">log1p(...)</a></td><td>  Returns log(1 + number), computed in a way that is accurate even when the value of number is close to zero</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.log.php">log(...)</a></td><td>  Natural logarithm</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.max.php">max(...)</a></td><td>  Find highest value</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.min.php">min(...)</a></td><td>  Find lowest value</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.mt-getrandmax.php">mt_getrandmax()</a></td><td>  Show largest possible random value</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.mt-rand.php">mt_rand()</a></td><td>  Generate a better random value</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.mt-srand.php">mt_srand(...)</a></td><td>  Seed the better random number generator</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.octdec.php">octdec(...)</a></td><td>  Octal to decimal</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.pi.php">pi()</a></td><td>  Get value of pi</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.pow.php">pow(...)</a></td><td>  Exponential expression</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.rad2deg.php">rad2deg(...)</a></td><td>  Converts the radian number to the equivalent number in degrees</td></tr>
                <tr><td>rand()</td><td>  Generate a random number 0 to 1.  Calls lcg_value (does not use the PHP rand function).  Use random_int if you want an integer.</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.random-int.php">random_int(...)</a></td><td>  Generates a random integer</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.round.php">round(...)</a></td><td>  Rounds a float. LabPal uses round for negative precisions and number_format for positive precisions.</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.sin.php">sin(...)</a></td><td>  Sine</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.sinh.php">sinh(...)</a></td><td>  Hyperbolic sine</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.sqrt.php">sqrt(...)</a></td><td>  Square root</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.srand.php">srand(...)</a></td><td>  Seed the random number generator</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.tan.php">tan(...)</a></td><td>  Tangent</td></tr>
                <tr><td><a target="_blank" href="https://php.net/manual/en/function.tanh.php">tanh(...)</a></td><td>  Hyperbolic tangent</td></tr>
                <th id="mathjax">MathJax</th><th></th>
                <tr><td>\$...\$</td><td>Expressions wrapped in dollar signs will be processed in-line as math expressions. <br>
                        For example, <code>$\frac{x^2}{2}$</code> becomes $\frac{x^2}{2}$ in the same line</td></tr>
                <tr><td>\$\$...\$\$</td><td>Expressions wrapped in double dollar signs will be processed as displayed equations. <br>
                        For example, <code>$$\frac{x^2}{2}$$</code> becomes $$\frac{x^2}{2}$$ on its own line.</td></tr>
                <tr><td colspan="2"> <a href="http://www.onemathematicalcat.org/MathJaxDocumentation/TeXSyntax.htm#R" target="_blank">Complete List of MathJax TeX commands</a></td></tr>
                <tr><td colspan="2"> <a href="https://www.mathjax.org/?page_id=13#demo" target="_blank">MathJax Dynamic Preview</a></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="computed_help">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Computed Variables</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>You can generate different values for each student using the computed variable type.  Enter an equation in the description field that describes the variable calculation. Some helpful functions are listed below.</p>
                <p>To make the value of the computed variable appear to the student, you must list it in the description enclosed as ##variableName##.  Assuming you have the variables named x and y, the following</p>
                <div class="card card-body bg-light">What is ##x## + ##y##?</div>
                <p class="mt-3">would render as</p>
                <div class="card card-body bg-light">What is 5 + 9?</div>
                <p class="mt-3">if the formulas for x and y looked something like the following:</p>
                <div class="card card-body bg-light">random_int(1,10)</div>
                <p class="mt-3">The first time that a user loads an assignment, values will get generated and stored for the student. Those same values will persist even if the user refreshes the page.</p>
                <p>You can choose whether students will get a button to generate new values by toggling the "Allow Value Refresh" setting on any question that contains computed variables.</p>
                <p>You can write any LabPal-valid equation in the description field for generating computed variables.  Please note that the rand() function in LabPal generates a random decimal between 0 and 1.  This is more like Excel's behavior than that listed in the PHP documentation; LabPal's rand() calls lcg_value().</p>
                <div class="card card-header"><strong>Example Formula</strong></div>
                <div class="card card-body">round(rand()*10,2)</div>
                <div class="card card-footer">would generate something like 4.32</div>
                <p class="mt-3"><strong>Some Useful Functions:</strong></p>
                <table class="table">
                    <tbody>
                    <tr><td>rand()</td><td>Random decimal value between 0 and 1</td></tr>
                    <tr><td>random_int(min,max)</td><td>Random integer between min and max</td></tr>
                    <tr><td>random_element(value1,value2,...)</td><td>Selects a random value from the list provided</td></tr>
                    <tr><td>random_float(min,max)</td><td>Random decimal number between min and max</td></tr>
                    <tr><td>round(value,digits)</td><td>Round number to a specified number of decimal places</td></tr>
                    <tr><td>sigs(value,digits)</td><td>Round number to a specified number of significant digits*</td></tr>
                    <tr><td>addNoise(var,scale,[iterations])</td><td>Adds random noise to a value or variable (including for each element of array variables) scaled by the scale value.  Iterations is optional.  See additional notes below.</td></tr>
                    </tbody>
                </table>
                <p>*Note that values smaller than 10<sup>-2</sup> or larger than 10<sup>2</sup> are rendered in scientific notation (E notation in arrays).</p>
                <p>You can use the alternative delimiter #_variableName_# to prevent the values from being rendered in scientific notation.</p>
                <p>You can use the alternative delimiter #^variableName^# to always render the values in scientific notation.</p>
                <hr>
                <p>The <strong>addNoise</strong> function adds a random noise to a value or array of values (each value in the array getting a different noise offset). The noise is generated by adding a random number between 0 and 1 repeatedly for the number of iterations (which is 1 if you don't specify otherwise).   Once the noise is generated, the original value is updated using the formula value + scale*(noise-iterations/2). If iterations is 1, the range of the noise will be var$\pm$scale/2 and the standard deviation of the distribution of values around var will be $scale/\sqrt{12}$.  If iterations is 12, the standard deviation will be equal to the scale value, such that addNoise(var,1,12) will generate noise with a standard deviation of 1 in the range var$\pm$6. Using iterations = 1 guarantees that no noisy value will exceed $\pm$scale/2.  Using larger iterations will approximate noise with a Gaussian distribution.  See <a target="_blank" href="https://www.dspguide.com/ch2/6.htm">this link</a> for more details on the statistics.</p>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="molecule_help">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Molecule Questions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h2>Evaluation type: Structure</h2>
                <p>Student structures must match the instructor structures.</p>
                <p><strong>Explicit H</strong> Requires students to include hydrogens explicitly. The question author can leave the hydrogens implicit.</p>
                <p><strong>Lone Pairs</strong> The lone pairs must match the answer. The question author must include the lone pairs.</p>
                <h3>Match Types</h3>
                <table class="table">
                    <tbody>
                    <tr><td>Single</td><td>Student can draw only one structure. It must match the given structure. Answers should include only one structure.</td></tr>
                    <tr><td>Any</td><td>Student can draw only one structure. It must match any of the answer structures.  Answer should include multiple structures.</td></tr>
                    <tr><td>All</td><td>Student must draw all answer structures.</td></tr>
                    <tr><td>Some</td><td>Student must draw a subset of the answer structures. Set the required number using the Required Structures Field.</td></tr>
                    </tbody>
                </table>
                <h2>Evaluation type: Formula</h2>
                <p>Student structures must match the molecular formula of the instructor structure. Students can draw multiple structures.  Set the required number using the Required Structures Field. With the current feature set, you should draw only one answer structure. You can select functional groups after choosing a functional group match type. Regardless of functional group requirements, all student structures must match the instructor molecular formula. You can require lone pairs without drawing them into your solution; this may not work for all elements, so make sure you test your solutions.</p>
                <h3>Functional Group Match Types</h3>
                <table class="table">
                    <tbody>
                    <tr><td>Ignore</td><td>Only parses molecular formula.</td></tr>
                    <tr><td>Any</td><td>Any of the selected functional groups must appear in the student canvas.</td></tr>
                    <tr><td>All</td><td>Any of the selected functional groups must appear in the student canvas (regardless of whether each molecule has one).</td></tr>
                    <tr><td>Each</td><td>Each student structure must contain any one of the selected functional groups.</td></tr>
                    <tr><td>Each</td><td>Each student structure must contain any one of the selected functional groups AND all selected functional groups must appear on the student canvas.</td></tr>
                    </tbody>
                </table>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="reaction_help">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Chemical Reaction Questions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>The chemical reaction question type allows students to input an arbitrary number of reactants and products for comparison against a solution. It will check formulas (including charge), phase labels (optional), and the reaction balance.</p>
                <p>Note that in balancing, students may enter fractional values (e.g. 5/2), which will evaluate the same as if they entered a decimal equivalent.</p>
                <p><strong>Credit Options</strong></p>
                <ul>
                    <li>No Partial Credit - Credit is only awarded if all parts of the question are answered correctly.</li>
                    <li>Basic Partial Credit - Credit is divided evenly based on answering the following portions correctly: reactant formulas (including charges), product formulas (including charges), reactant phases (if included), product phases (if included), and reaction balance. Note that the phases are only evaluated if all formulas on one side of the reaction are correct and the balance is only evaluated if all formulas are correct.</li>
                    <li>Specified Partial Credit - Works like basic partial credit, but the fractions are defined by the author instead of evenly divided. The fractions must add to 1 and can be entered in decimal (0.5) form or fractional form (1/2).</li>
                </ul>
                <p><strong>Feedback Options</strong></p>
                <ul>
                    <li>Minimal Feedback - Only the specified feedback statements are given to the student.</li>
                    <li>Directed Feedback - In addition to the feedback statements indicated, some additional feedback will be given about what is wrong with the reaction.</li>
                </ul>
                <p><strong>Balancing Options</strong></p>
                <ul>
                    <li>Any Matching Balance Ratio - As long as the reaction is properly balanced, it is accepted regradless of the magnitude of the coefficients.  A + 2B -> 3C will evaluate the same as 2A + 4B -> 6C</li>
                    <li>These Exact Coefficients - The coefficients provided in the answer must be matched exactly.</li>
                </ul>
                <p>Formulas currently only offer an exact matching option. CH2O and H2CO will not match. Molecular formula matching is a goal for this question type, but is a nontrivial exercise.</p>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal for reordering questions -->
<div class="modal fade" id="question_order_modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionOrderModalLabel">Question Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Drag the questions to reorder and then press the apply button.</p>

                <ul class="list-group" id="question_order_list">
                    [[#questions:q]]
                    <li class="list-group-item py-0" data-id="[[q]]">
                        [[name]]
                    </li>
                    [[/questions]]
                </ul>
                <div class="centered">
                    <button class="btn btn-sm btn-success mt-2" on-click="applyQuestionOrder">Apply Order</button>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal for editing selection variable -->
<div class="modal fade" id="selection_variable_modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Selection Variable</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Variable: </strong>[[selectionVariable.name]]</p>
                <p><strong>Title: </strong>[[selectionVariable.title]]</p>

                <ul class="list-group" id="selection_list">
                    [[#selectionVariable.choices:c]]
                    <li class="list-group-item py-0 p-0" data-id="[[c]]">
                        <form class="form-inline">
                            <span class="input-group-text choice-handle mb-2 mr-1"><i class="fas fa-arrows-alt"></i> </span>
                            <span class="sr-only" for="choice_[[c]]_value">Value</span>
                            <textarea type="text" rows="1" class="form-control mb-2 mr-sm-2" id="choice_[[c]]_value" placeholder="Value" value="[[value]]"></textarea>
                            <label class="sr-only" for="choice_[[c]]_name">Name</label>
                            <textarea type="text" rows="1" class="form-control mb-2 mr-sm-1" id="choice_[[c]]_name" placeholder="Name" value="[[name]]"></textarea>
                            <button class="btn btn-outline-secondary mb-2" aria-label="Remove" onclick="return false;" on-click="['removeVariableChoice',c]"><i class="far fa-trash-alt" aria-hidden="true" title="Remove"></i></button>
                        </form>
                    </li>
                    [[/selectionVariable.choices]]
                </ul>
                <div class="centered">
                    <button class="btn btn-sm btn-success" on-click="addVariableChoice">Add Choice</button>
                    <br/>
                    <button class="btn btn-sm btn-light" on-click="copySelectionJSON">Copy</button>
                    <button class="btn btn-sm btn-light" on-click="pasteSelectionJSON">Paste</button>
                    [[#selectionVariable.pasteJSON]]
                    <div class="alert alert-danger">You can click the copy button on a selection variable to get choices to paste here. You are strongly discouraged from trying to edit the content that you paste here.</div>
                    <textarea class="form-control" id="paste_selection_JSON_textarea">[[selectionVariable.pasted_JSON]]</textarea>
                    <button class="btn btn-sm btn-success" on-click="importSelectionJSON">Import Pasted</button>
                    [[/selectionVariable.pasteJSON]]
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal for unlinking assignment -->
<div class="modal fade" id="unlink_modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">Unlink Assignment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Unlinking this assignment will prevent it from receiving any additional updates from the parent assignment. This action cannot be undone.</p>
                <p>Are you sure you want to unlink this assignment?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <a href="unlink" class="btn btn-danger">Unlink</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal for getting raw JSON of question -->
<div class="modal fade" id="question_JSON_modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="JSONModalLabel">Copy Question Content</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">You are strongly discouraged from trying to edit the content that you copy from here.</div>
                <textarea class="form-control" id="question_JSON_textarea">[[question_JSON]]</textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" on-click="copyJSON">Copy to Clipboard</button>
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal for getting raw JSON of question -->
<div class="modal fade" id="paste_JSON_modal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pasteJSONModalLabel">Paste Question</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>You can paste question objects here that you can obtain by clicking the <i class="fas fa-external-link-square-alt" aria-hidden="true"></i><span class="sr-only">Export JSON</span> button on the top of a question.</p>
                <div class="alert alert-danger">You are strongly discouraged from trying to edit the content that you paste here.</div>
                <textarea class="form-control" id="paste_JSON_textarea">[[pasted_JSON]]</textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" on-click="importJSON">Import</button>
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
