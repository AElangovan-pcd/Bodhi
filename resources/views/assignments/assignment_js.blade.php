

<script>

    var qdata = {!! $qdata !!};
    var graderPath = "{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/evaluate')}}";
    var moleculeGraderPath = "{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/evaluateMolecule')}}";
    var submitWrittenAnswerPath = "{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/submitWrittenAnswer')}}";
    var previewWrittenAnswerPath = "{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/previewWrittenAnswer')}}";
    var submitCommentPath = "{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/submitComment')}}";

    // Constants
    qdata.STANDARD_QUESTION = 1;
    qdata.SHORT_ANSWER = 2;
    qdata.SIMPLE_QUESTION = 3;
    qdata.UNANSWERED_QUESTION = 4;
    qdata.SIMPLE_TEXT_QUESTION = 5;
    qdata.MOLECULE_QUESTION = 6;
    qdata.MULTIPLE_CHOICE_QUESTION = 7;
    qdata.REACTION_QUESTION = 8;

    //Loop through each of the question descriptions to insert file references

    var file_regex = /#!(.*?)!#/g;  //File list
    var img_regex = /#i!(.*?)#(.*\S.*)!i#/g; //Refer to image in files.
    var assignment_regex = /#~(.*?)~#/g;

    var file_ref = '../../files/download/';  //Relative link location

    if(qdata.student_view)
        file_ref = '../../../../files/download/'; //For student views

    if(qdata.questions.length > 0) {
        for (i = 0; i < qdata.questions.length; i++) {
            try {
                let link;
                qdata.questions[i].description = qdata.questions[i].description.replace(file_regex, function (match, p1) {
                    if (qdata.fileList[p1] === undefined)
                        return p1;
                    link = '<a href="' + file_ref + qdata.fileList[p1] + '">' + p1 + '</a>';
                    return link;
                });
                qdata.questions[i].description = qdata.questions[i].description.replace(assignment_regex, function (match, p1) {
                    if (qdata.assignmentList[p1] === undefined)
                        return p1;
                    let link = '<a href="../../assignment/'+qdata.assignmentList[p1]+'/view">'+p1+'</a>';
                    return link;
                });
                qdata.questions[i].description = qdata.questions[i].description.replace(img_regex, function (match, p1,p2) {
                    if (qdata.fileList[p1] === undefined)
                        return p1;
                    link = '<img class="img-fluid" title="'+p2+'" alt="'+p2+'" src="' + file_ref + qdata.fileList[p1] + '">';
                    console.log(p1,p2);
                    return link;
                });
            }
            catch (e) {
                console.log(e);
            }
        }
    }

    //Init missing answer for multiple choice questions
    qdata.questions.forEach(question => {
        if(question.answer == null)
            question.answer = {submission: ""};

        //parse variables for chemical formulas
        question.variables.forEach(v => {
            if(parseInt(v.type) === 5 && v.answer !== null && v.answer.submission !== undefined)
                v.answer.submission = JSON.parse(v.answer.submission);
        })
    });

    qdata.choiceName = function(q,v,value) {
        let variable = qdata.questions[q].variables[v];
        if(value === undefined)
            return 'Select one';
        let choice = variable.choices.find(x => x.value.toString() === value.toString());
        if(choice === undefined)
            return 'Select one';
        return choice.name;
    };

    qdata.chargeDefault = function(value) {
        return (value == null ? 'Charge' : value);
    };

    qdata.phaseDefault = function(value) {
        return (value == null ? 'Phase' : value);
    }

    //Format quiz times for readability
    if(qdata.assignment.type === 2 ) {
        try {
            qdata.quiz_controls.allowed_start = format_time(qdata.quiz_controls.allowed_start);
            qdata.quiz_controls.allowed_end = format_time(qdata.quiz_controls.allowed_end);
            qdata.quiz_controls.loaded_time = format_time(qdata.quiz_controls.loaded_time);

            if (qdata.quiz_controls.actual_start != null)
                qdata.quiz_controls.actual_start = format_time(qdata.quiz_controls.actual_start);
        }
        catch(e) {}
    }

    function format_time(str) {
        console.log(moment(str).format('dddd MMMM Do YYYY, h:mm a'));
        return moment(str).format('dddd MMMM Do YYYY, h:mm a');
    }

    qdata.kekules = [];

    function getSignificantDigitCount(n) {
        n = Math.abs(String(n).replace(".", "")); //remove decimal and make positive
        if (n == 0) return 0;
        while (n != 0 && n % 10 == 0) n /= 10; //kill the 0s at the end of n

        return Math.floor(Math.log(n) / Math.log(10)) + 1; //get number of digits
    }

    var ractive2 = new Ractive({
        target: '#qtarget',
        template: '#qtemplate',
        data: qdata,
        delimiters: [ '[[', ']]' ],
        computed: {
            questionNumbers: function () {
                let questions = this.get('questions');
                let computed = [];
                let cnt = 0;
                questions.forEach(q => {
                        if(q.options != null && q.options.excludeFromNumbering === true)
                            computed.push(0);
                        else {
                            cnt++;
                            computed.push(cnt)
                        }
                    }
                );
                return computed;
            },
            unAnsweredCount: function() {
                let questions = this.get('questions');
                let cnt = 0;
                questions.forEach(q => {
                    if (q.type !== qdata.UNANSWERED_QUESTION) {
                        if (q.result == null || (q.result.id === undefined && (q.result.valid === undefined || q.result.valid === false)))
                            cnt++;
                    }
                });
                return cnt;
            }
        },
    });

    //parse variables for chemical formulas
    qdata.questions.forEach((question,q) => {
        question.variables.forEach((variable, v) => {
            if(parseInt(variable.type) === 5 && variable.answer !== null && variable.answer.submission !== undefined)
                preview_formula(q,v,variable.answer.submission);
        });
        if(parseInt(question.type) === 8) {
            preview_reaction_assignment(q);
        }
    });

    ractive2.on('gradedFlagFilter', function(context) {
        this.toggle('gradedFlagFilter');
        MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
    });

    //Loop through questions to insert Kekule composers on molecule questions
    //Also insert short answer summernote editors
    for (i = 0; i < qdata.questions.length; i++) {
        if(qdata.questions[i].type==qdata.SHORT_ANSWER) {
            writtenEditor(i,qdata.questions[i]);
        }
        else if(qdata.questions[i].type==qdata.MOLECULE_QUESTION) {
            var composer = new Kekule.Editor.Composer(document.getElementById('moleculeEditor_' + i));
            composer.getEditorConfigs().getInteractionConfigs().setAllowUnknownAtomSymbol(false); //Don't allow user to type in unknown atom names (e.g. 'o' instead of 'O')
            customizeMoleculeEditor(composer,qdata.questions[i].molecule.editor);
            if(qdata.questions[i].result != null && qdata.questions[i].answer.submission != "")
                composer.setChemObj(Kekule.IO.loadFormatData(qdata.questions[i].answer.submission, 'Kekule-JSON'));

            kekules = ractive2.get("kekules");

            kekules[i] = composer;
            ractive2.set("kekules", kekules);
        }
    }


    function customizeMoleculeEditor(composer, editorType) {
        console.log(editorType);
        if(editorType==='simple') {
            var N = Kekule.ChemWidget.ComponentWidgetNames;
            var C = Kekule.Editor.ObjModifier.Category;

            // Common toolbar buttons
            composer.setCommonToolButtons([
                N.newDoc,
                N.undo,
                N.redo,
                N.copy,
                N.cut,
                N.paste,
                N.zoomIn,
                N.zoomOut
            ]);

            // Chem toolbar buttons
            composer.setChemToolButtons([
                {
                    "name": N.manipulate,
                    "attached": [
                        N.manipulateMarquee,
                        N.manipulateLasso,
                        N.manipulateAncestor,
                        N.dragScroll,
                        N.toggleSelect
                    ]
                },
                N.erase,
                {
                    "name": N.molBond,
                    "attached": [
                        N.molBondSingle,
                        N.molBondDouble,
                        N.molBondTriple
                    ]
                },
                {
                    "name": N.molAtomAndFormula,
                    "attached": [
                        N.molAtom
                    ]
                },
                {
                    "name": N.molRing,
                    "attached": [
                        N.molRing3,
                        N.molRing4,
                        N.molRing5,
                        N.molRing6,
                        N.molFlexRing,
                        N.molRingAr6,
                        N.molRepCyclopentaneHaworth1,
                        N.molRepCyclohexaneHaworth1,
                        N.molRepCyclohexaneChair1,
                        N.molRepCyclohexaneChair2
                    ]
                },
                {
                    "name": N.molCharge,
                    "attached": [
                        N.molChargeClear,
                        N.molChargePositive,
                        N.molChargeNegative,
                        N.molRadicalDoublet,
                        N.molElectronLonePair
                    ]
                }
            ]);

            // Object modifiers
            composer.setAllowedObjModifierCategories([C.GENERAL, C.CHEM_STRUCTURE, C.GLYPH, C.MISC]);
        }
    }

    function writtenEditor(i,question) {
        var id = question.id;
        if(question.answer == null)
            question.answer = {submission: ""};
        text = question.answer.submission;
        var div = '#written_answer_' + (id);
        console.log("init " + div);
        $(div).summernote({
            placeholder: 'Your answer...',
            tabDisable: false,
            height: 100,
            toolbar: [
                ["style", ["bold", "italic", "underline"]],
                ["font", ["strikethrough", "superscript", "subscript"]],
                ["fontsize", ["fontsize"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["picture"],
            ],
            popover: {
                image: [
                    ['custom', ['imageAttributes']],
                    //['imagesize', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                    //['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']]
                ],
            },
            imageAttributes:{
                icon:'<i class="note-icon-pencil"/>',
                removeEmpty:false, // true = remove attributes | false = leave empty if present
                disableUpload: false // true = don't display Upload Options | Display Upload Options
            },
            callbacks: {
                onImageUpload: function(files) {
                    summernoteOnImageUpload(files,div);
                },
                onChange: function(contents, $editable) {
                    ractive2.set("questions["+i+"].answer.submission", contents);
                }
            }
        });
        $(div).summernote('code',text);
        if(question.result != null && question.result.status !=2)
            $(div).summernote('disable');
    }

    ractive2.on("evalQuestion", function(context,intermediates) {
        @if(isset($user))
        if(ractive2.get("student_view")&&ractive2.get("submit_for_student")) {
            graderPath = "{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/evaluate_for_user/'.$user->id)}}";
            console.log("Submitting for student.");
        }
        else
            graderPath = "{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/evaluate')}}";
            @endif

        var qid = context.node.id;
        var ind = context.node.name;

        //Set message that the question is being processed.
        var questions = ractive2.get("questions");
        if(questions[ind].result == null)
            questions[ind].result = {};
        questions[ind].result.feedback = "Processing question...";
        questions[ind].result.error = true;
        ractive2.set("questions",questions);
        $('#result_'+ind).html("Processing question...");  //Need to clear the div entirely to deal with Ractive MathJax conflict.

        //Get the variables for the question.
        try {
            let processed_vars = getQuestionVars(questions,ind);
            if(processed_vars.status === 'error')  //End execution if there is an error.
                throw processed_vars.msg;
            var question_vars = processed_vars.question_vars;
            console.log("variables",question_vars);
        }
        catch(err) {
            questions[ind].result.feedback = "Error processing your inputs. "+err;
            questions[ind].result.error = true;
            ractive2.set("questions",questions);
            $('#result_'+ind).html("Error processing your inputs. "+err);
            console.log(err);
            return;
        }

        //Send to the backend for evaluation
        $.post(graderPath,
            {
                _token: "{{ csrf_token() }}",
                question: qid,
                values: JSON.stringify(question_vars),
            })
            .done(function (response) {  //After successful post response from evaluation
                response = JSON.parse(response);
                console.log(response);

                questions[ind].result = response;
                if(response.valid) {
                    questions[ind].result.error = false;
                    if(intermediates)
                        questions[ind].intermediates = response.interVars;
                }
                else {
                    questions[ind].result.error = true;
                    if(response.output.indexOf("Unknown identifier")!==-1)
                        response.output+="<br/>This usually means that you have not evaluated a previous question that is needed for this question. Evaluate or re-evaluate any relevant questions above."
                }

                ractive2.set("questions",questions);

                console.log(questions);
                $('#result_'+ind).html(response.output);  //Deal with Ractive MathJax conflict.
                MathJax.Hub.Queue(["Typeset",MathJax.Hub]);

            })
            .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                questions[ind].result.error = true;
                questions[ind].result.feedback = "Error processing question. Try reloading the page and check your inputs.";
                ractive2.set("questions",questions);
                $('#result_'+ind).html("Error processing question. Try reloading the page and check your inputs.");
                console.log(error);
            });
    });

    ractive2.on("evalMoleculeQuestion", function(context) {
        @if(isset($user))
        if(ractive2.get("student_view")&&ractive2.get("submit_for_student")) {
            moleculeGraderPath = "{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/evaluate_molecule_for_user/'.$user->id)}}";
            console.log("Submitting for student at "+graderPath);
        }
        else
            moleculeGraderPath = "{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/evaluateMolecule')}}";
            @endif

        var qid = context.node.id;
        var ind = context.node.name;

        //Set message that the question is being processed.
        var questions = ractive2.get("questions");
        if(questions[ind].result == null)
            questions[ind].result = {};
        questions[ind].result.feedback = "Processing question...";
        questions[ind].result.error = true;
        ractive2.set("questions",questions);
        $('#result_'+ind).html("Processing question...");  //Need to clear the div entirely to deal with Ractive MathJax conflict.

        var kekules = ractive2.get("kekules");
        composer = kekules[ind];
        //var mols = composer.exportObjs(Kekule.Molecule);
        var mols = composer.exportObjs(Kekule.StructureFragment);

        if(mols.length === 0) {
            $('#result_'+ind).html("You haven't drawn any structures.");
            questions[ind].result.error = true;
            ractive2.set("questions",questions);
            return;
        }
        else if(mols.length > 1 && questions[ind].molecule.evalType === 'structure' && (questions[ind].molecule.matchType === 'single' || questions[ind].molecule.matchType === 'any')) {
            $('#result_'+ind).html("Please draw only one molecule for this question. You drew "+mols.length +".");
            questions[ind].result.error = true;
            ractive2.set("questions",questions);
            return;
        }

        var ansDrawing = Kekule.IO.loadFormatData(questions[ind].molecule.drawing, 'Kekule-JSON');
        var ansComposer = new Kekule.Editor.Composer(document);
        ansComposer.setChemObj(ansDrawing);
        //ansMols = ansComposer.exportObjs(Kekule.Molecule);
        ansMols = ansComposer.exportObjs(Kekule.StructureFragment);

        var comparison = compare_drawings(questions[ind], mols, ansMols);
        var submission = Kekule.IO.saveFormatData(composer.getChemObj(), 'Kekule-JSON');

        let submitData = {
            _token: "{{ csrf_token() }}",
            question: qid,
            submission: submission,
            comparison: comparison.correct,
            feedback: comparison.feedback,
        };

        if(questions[ind].molecule.evalType === 'formula') {
            submitData['numMatches'] = comparison.numMatches;
            submitData['groups'] = comparison.groups;
        }


        $.post(moleculeGraderPath, submitData
        )
            .done(function (response) {  //After successful post response from evaluation
                response = JSON.parse(response);
                console.log(response);

                questions[ind].result = response;
                if(response.valid)
                    questions[ind].result.error = false;
                else
                    questions[ind].result.error = true;
                ractive2.set("questions",questions);
                console.log(questions);
                $('#result_'+ind).html(response.output);  //Deal with Ractive MathJax conflict.
                MathJax.Hub.Queue(["Typeset",MathJax.Hub]);

            })
            .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                questions[ind].result = "Error processing question. Try reloading the page and check your inputs.";
                questions[ind].result.error = true;
                ractive2.set("questions",questions);
                $('#result_'+ind).html("Error processing question. Try reloading the page and check your inputs.");
                console.log(error);
            });
    });

    function compare_drawings(question, subDraw, ansDraw) {
        if(question.molecule.evalType === 'structure') {
            if (question.molecule.matchType === 'single') {
                var sub = subDraw[0];
                var ans = ansDraw[0];

                return do_comparison(question, sub, ans);
            }
            if (question.molecule.matchType === 'any') {
                var sub = subDraw[0];
                var comparison;
                for (var j = 0; j < ansDraw.length; j++) {
                    comparison = do_comparison(question, sub, ansDraw[j]);
                    if (comparison.correct)
                        return comparison;
                }
                return comparison;
            }
        }
        if(question.molecule.matchType === 'all' || question.molecule.matchType === 'some' || question.molecule.evalType === 'formula') {
            var numMatches = 0;
            var numFails = 0;
            var subMatches = 0;
            var comparison, subComparison, groupComparison;
            var groupMatches = 0;
            var groupFails = 0;
            var allMatchingGroups = [];
            let identicals = false;
            let lonePairFlag = false;
            let lonePairFails = 0;
            var validAns = false;
            var answerLength = ansDraw.length;
            let halogenMsg = '';

            if(question.molecule.evalType === 'formula') {
                answerLength = 1;
            }
            for(k=0; k<subDraw.length; k++) {
                let skip = false;
                for(let m=0; m<k; m++) { //Check for existing identical structure
                    if(m !== k) {
                        comparison = compare_molecules(question, subDraw[k], subDraw[m]);
                        if(comparison.correct) {
                            identicals = true;
                            skip = true;
                        }
                    }
                }
                if(skip)
                    continue;

                for(j=0; j<answerLength; j++) {
                    comparison = do_comparison(question, subDraw[k], ansDraw[j]);

                    if (comparison.correct) {
                        numMatches++;
                        validAns = true;
                    }
                    if (comparison.flag_pair_violation) {
                        lonePairFlag = true;
                        numMatches++;
                        lonePairFails++;
                        validAns = true;
                    }
                    if(comparison.terminal_halogen_violation)
                        halogenMsg = ' In most molecules, halogens are terminal atoms (they are only bonded to one other atom via a single bond). This question requires that halogens are only used as terminal atoms.';
                    if(question.molecule.evalType === 'formula' && ansDraw.length>1) {
                        subComparison = subStructSearches(question, ansDraw[j], ansDraw);
                        if (subComparison)
                            subMatches++;
                        else if (ansDraw.length > 1 && !subComparison)
                            validAns = false;
                    }
                }
                groupComparison = compare_groups(question, comparison.groups, allMatchingGroups);
                if(groupComparison.valid)
                    groupMatches++;
                else if(question.molecule.groups && question.molecule.groups.length > 0 && !groupComparison.valid) {
                    groupFails++;
                }
                if(!validAns)
                    numFails++;
                validAns = false;
            }

            let identicalMsg = "";

            if(identicals)
                identicalMsg = " Some of your structures are identical. Structures can look different on paper, but are simply rotated in some way."

            let lonePairMsg = "";
            if(lonePairFlag)
                lonePairMsg = " You have not drawn your lone pairs correctly, which is required for this question.";

            let groups = {groupMatches: groupMatches, groupFails: groupFails, groupsMatched: groupComparison.allMatchingGroups.length};
            if(question.molecule.evalType === 'formula') {
                var functional_check = check_group_requirements(question, groupMatches, groupFails, groupComparison.allMatchingGroups.length);
                if (numFails === 0 && numMatches >= question.molecule.structureNum) {
                    if(functional_check.valid && !lonePairFlag)
                        return {correct: 'true', feedback: 'Correct!' + identicalMsg + lonePairMsg + halogenMsg, numMatches: numMatches, groups: groups};
                    else
                        return {
                            correct: 'false',
                            feedback: 'You have drawn the required number of structures with the correct molecular formula. '
                                +functional_check.msg + identicalMsg + lonePairMsg + halogenMsg,
                            numMatches: numMatches - lonePairFails,
                            groups: groups
                        };
                }
                return {
                    correct: 'false',
                    feedback: 'You drew ' + numMatches + ' that match' + (numMatches === 1 ? 'es' : '') + ' the molecular formula. You drew ' + numFails + ' that ' + (numFails === 1 ? 'does' : 'do') + ' not match the molecular formula. You need to draw ' + question.molecule.structureNum + ' with the correct molecular formula. '
                        +functional_check.msg + identicalMsg + lonePairMsg + halogenMsg,
                    numMatches: numMatches - lonePairFails,
                    groups: groups
                };
            }
            if(question.molecule.matchType ==='all') {
                if (numFails === 0 && numMatches === ansDraw.length && !lonePairFlag)
                    return {correct: 'true', feedback: 'Correct!' + identicalMsg + lonePairMsg + halogenMsg,numMatches: numMatches, groups: groups};
                return {
                    correct: 'false',
                    feedback: 'You drew ' + numMatches + ' that match' + (numMatches === 1 ? 'es' : '') + ' the requirements. You drew ' + numFails + ' that ' + (numFails === 1 ? 'does' : 'do') + ' not match the requirements. You need to draw ' + ansDraw.length + ' correctly.' + identicalMsg + lonePairMsg + halogenMsg,
                    numMatches: numMatches - lonePairFails,
                    groups: groups
                };
            }
            else if(question.molecule.matchType === 'some') {
                if (numFails === 0 && numMatches >= question.molecule.structureNum && !lonePairFlag)
                    return {correct: 'true', feedback: 'Correct!' + identicalMsg,numMatches: numMatches, groups: groups};
                return {
                    correct: 'false',
                    feedback: 'You drew ' + numMatches + ' that match' + (numMatches === 1 ? 'es' : '') + ' the requirements. You drew ' + numFails + ' that ' + (numFails === 1 ? 'does' : 'do') + ' not match the requirements. You need to draw ' + question.molecule.structureNum + ' correctly.' + identicalMsg + lonePairMsg + halogenMsg,
                    numMatches: numMatches - lonePairFails,
                    groups: groups
                };
            }
        }
    }

    function check_group_requirements(question, groupMatches, groupFails, groupsMatched) {
        var valid = false;
        var msg="";
        if(question.molecule.groupMatchType === 'any' && groupMatches === 0)
            msg = "You have not drawn a required functional group.";
        else if((question.molecule.groupMatchType === 'all' || question.molecule.groupMatchType === 'each/all') && groupsMatched !== question.molecule.groups.length)
            msg = "You have not drawn all of the required functional groups. You drew "+groupsMatched+" of the required functional groups. You need to draw "+ question.molecule.groups.length+".";
        else if((question.molecule.groupMatchType === 'each' || question.molecule.groupMatchType === 'each/all') && groupFails > 0)
            msg = "Each of your structures needs to have one of the required functional groups. You drew "+groupMatches+" structures with a required functional group and "+groupFails+" stuctures without a required functional group.";
        else
            valid = true;
        return {valid, msg};

    }

    function do_comparison(question, sub, ans) {
        if(question.molecule.evalType === 'formula')
            return compare_formulas(question, sub, ans);
        else if(question.molecule.evalType === 'structure')
            return compare_molecules(question, sub, ans);
    }

    function compare_molecules(question, sub, ans) {
        var structure = sub.isSameStructureWith(ans);
        var hydrogens = count_hydrogens(sub);
        var lonePairs = sub.isSameStructureWith(ans,{'lonePair':question.molecule.lonePairs});
        var explicitH = hydrogens.explicitH === hydrogens.totalH;
        console.log("structure ",structure);
        if(!structure)
            return {correct: false, feedback: 'Incorrect Structure'};
        if(question.molecule.explicitH && !explicitH)
            return {correct: false, feedback: 'This question requires you to draw all hydrogens explicitly.'};
        if(question.molecule.lonePairs && !lonePairs)
            return {correct: false, feedback: 'You have not drawn your lone pairs correctly, which is required for this question.'};
        return {correct: true, feedback: 'Correct!'};
    }

    function compare_formulas(question, sub, ans) {
        parse_sub = parse_molecule(sub, question.molecule.lonePairs, question.molecule.halogens);
        parse_ans = parse_molecule(ans, false);

        if(parse_sub.shell_violation)
            return {correct: false, feedback: 'Your structure has a problem with the number of electrons on an atom.', groups: parse_sub.groups, flag_pair_violation:false};
        comparison = compare_atom_counts(parse_sub.atoms, parse_ans.atoms);
        if(!comparison)
            return {correct: false, feedback: 'Your structure does not have the correct molecular formula.',groups: parse_sub.groups, flag_pair_violation: false};
        if(parse_sub.terminal_halogen_violation)
            return {correct: false, feedback: 'In most molecules, halogens are terminal atoms (they are only bonded to one other atom via a single bond).', flag_halogen_violation: true, terminal_halogen_violation: true};
        if(parse_sub.pair_violation)
            return {correct: false, feedback: 'You have not drawn your lone pairs correctly, which is required for this question.', flag_pair_violation: true};
        return {correct: true, feedback: 'Correct!', groups: parse_sub.groups, flag_pair_violation: false};

    }

    function subStructSearches(question, srcMol, targetMols) {
        var valid = true;
        for(var i=1; i< targetMols.length; i++) {
            subFound = subStructSearch(srcMol, targetMols[i]);
            if(!subFound)
                valid = false;
        }
        return valid;
    }

    function subStructSearch(srcMol, targetMol) {
        var options = {
            'level': Kekule.StructureComparationLevel.CONSTITUTION,  // compare in consititution level
            'compareCharge': false,   // ignore charge
            'compareMass': false      // ignore mass number difference
        };
        // check if targetMol is a sub structure in srcMol
        var result = srcMol.search(targetMol, options) || [];

        return !!result.length;
    }

    function count_hydrogens(mol) {
        var flattenMol = mol.getFlattenedShadowFragment(true);
        implicitH = 0;
        totalH = 0;
        for (var i = 0, l = flattenMol.getNodeCount(); i <l; ++i) {
            let node = flattenMol.getNodeAt(i);
            if(!['O','N'].includes(node.getLabel()))
                implicitH += node.getImplicitHydrogenCount();  //Because O and N show H automatically, count them as explicit even if a bond is not drawn
            totalH += node.getHydrogenCount(true);
        }
        return {implicitH: implicitH, totalH: totalH, explicitH: totalH-implicitH};
    }

    function parse_molecule(mol, checkLonePairs = false, terminalHalogensOnly = false) {
        // iterate all nodes(atoms)
        var atoms = {'charge' : 0};
        var groups = {};
        var shell_violation = false;
        let pair_violation = false;
        let halogen_violation = false;
        let total_electrons = 0;
        let total_valence = 0;
        for (let i = 0, l = mol.getNodeCount(); i < l; ++i)
        {
            var node = mol.getNodeAt(i);
            var hydrogens = node.getHydrogenCount(false);
            var charge = node.charge;
            if(!check_shell(node))
                shell_violation = true;
            if(terminalHalogensOnly)
                if(checkTerminalHalogenViolation(node))
                    halogen_violation = true;

            //Check that the formal charge matches the assigned charge. Checks for H, C, N, O, and F.
            let formal_charge = check_formal_charge(node);
            if(formal_charge !== undefined && formal_charge !== charge)
                shell_violation = true;

            // Check that the lone pairs are correct if required. Checks for correct number of lone pairs on each atom
            // for H, C, N, O, and F and also verifies that the total number of electrons drawn matches the expected number.
            if(checkLonePairs) {
                let electrons = check_electrons(node);
                total_electrons += electrons.lonePairs*2 + node.getImplicitHydrogenCount()*2; //Only use the explicitly drawn lone pairs because bonds would get double-counted (once for each node connected to the bond). Implicit bonds to H are also counted here. Explicitly drawn bonds are added later.
                total_valence += electrons.valenceElectrons + node.getImplicitHydrogenCount(); //Add one valence electron for each bonded hydrogen
                if(electrons.pairViolation === true)
                    pair_violation = true;
            }

            //Get the atom counts for the molecular formula
            if(atoms[node.getLabel()] === undefined)
                atoms[node.getLabel()]=1;
            else
                atoms[node.getLabel()]++;
            if(hydrogens > 0) {
                if(atoms['H']===undefined)
                    atoms['H'] = hydrogens;
                else
                    atoms['H'] += hydrogens;
            }
            atoms['charge'] += charge;

            var group = check_functional_groups(mol,i);
            for(var j = 0; j<group.length; j++) {
                if (groups[group[j]] === undefined)
                    groups[group[j]] = 1;
                else
                    groups[group[j]]++;
            }

        }
        //If the total number of expected electrons is not equal to the number of drawn electrons when lone pairs are required, fail.
        if(checkLonePairs)
        //Get the electrons from explicitly drawn bonds. Not done in the node loop because bonded electrons would get double-counted (counted for each atom).
            for(let i=0; i<mol.getConnectorCount(); i++ ) {
                total_electrons += mol.getConnectorAt(i).bondOrder*2;
            }
        if(total_electrons !== total_valence)
            pair_violation = true;

        return {'shell_violation':shell_violation, 'pair_violation': pair_violation, 'terminal_halogen_violation': halogen_violation, 'atoms':atoms, 'groups':groups};
    }

    //Looks for the following functional groups:
    //alcohol, ether, ketone, aldehyde, acid, ester
    function check_functional_groups(mol,index) {
        var groups = [];
        var primaryNode = mol.getNodeAt(index);
        var neighborAtoms = primaryNode.getLinkedObjs();
        if(primaryNode.getLabel()==='C') {
            if (is_carbonyl(primaryNode)) {
                groups.push("carbonyl");
                var carbonyl_group = check_carbonyl_groups(primaryNode);
                if (carbonyl_group !== false)
                    groups.push(carbonyl_group);
            }
        }
        else if(primaryNode.getLabel()==='O') {
            var oxygen_group = check_oxygen_groups(primaryNode);
            if(oxygen_group !== false)
                groups.push(oxygen_group);
        }

        return groups;
    }

    function compare_groups(question, groups_object, allMatchingGroups) {
        var matchingGroupFound = false;
        var groups;
        if(groups_object === undefined)
            groups = [];
        else
            groups = Object.keys(groups_object);
        for(var i=0; i<groups.length; i++) {
            if(question.molecule.groups.includes(groups[i])) {
                matchingGroupFound = true;
                if(!(allMatchingGroups.includes(groups[i])))
                    allMatchingGroups.push(groups[i]);
            }
        }

        return {valid: matchingGroupFound, allMatchingGroups: allMatchingGroups};
    }

    function is_carbonyl(atom) {
        var bonds = atom.getLinkedConnectors();
        for(var i=0; i<bonds.length; i++) {
            if(bonds[i].getBondOrder() === 2) {
                var atoms = bonds[i].getConnectedObjs();
                for(var j=0; j<atoms.length; j++) {
                    if(atoms[j].getLabel() === 'O' && atoms[j].getLinkedConnectors().length === 1)
                        return true;
                }
            }
        }
        return false;
    }

    //Check for ketone, aldehyde
    function check_carbonyl_groups(atom) {
        var bonds = atom.getLinkedConnectors();
        if(bonds.length + atom.getImplicitHydrogenCount() !== 3)
            return false;
        var neighborAtoms = atom.getLinkedObjs();
        var C = 0;
        var H = atom.getHydrogenCount(true);
        for(var i=0; i<neighborAtoms.length; i++) {
            if (neighborAtoms[i].getLabel() === 'C')
                C++;
        }
        if(C === 2 && H === 0)
            return 'ketone';
        else if(C === 1 && H === 1)
            return 'aldehyde';
        return false;
    }

    //Check for alcohol, ether, ester, acid
    function check_oxygen_groups(atom) {
        var bonds = atom.getLinkedConnectors();
        for (var i =0; i< bonds.length; i++) {
            if(bonds[i].getBondOrder() === 2) //This is a carbonyl oxygen
                return false;
        }
        var neighborAtoms = atom.getLinkedObjs();
        var carbonyl_C = 0;
        var non_carbonyl_C = 0;
        var H = atom.getHydrogenCount(true);
        for(var i=0; i<neighborAtoms.length; i++) {
            if (neighborAtoms[i].getLabel() === 'C') {
                if(is_carbonyl(neighborAtoms[i]))
                    carbonyl_C++;
                else
                    non_carbonyl_C++;
            }
        }
        if(carbonyl_C === 1) {
            if(non_carbonyl_C === 1 && H === 0)
                return "ester";
            if(non_carbonyl_C === 0 && H === 1)
                return "acid";
        }
        if(carbonyl_C === 0) {
            if(non_carbonyl_C === 2 && H === 0)
                return "ether";
            if(non_carbonyl_C === 1 && H === 1)
                return "alcohol";
        }

        return false;
    }

    function compare_atom_counts(atoms1, atoms2) {
        var keys1 = Object.keys(atoms1);
        var keys2 = Object.keys(atoms2);

        if(keys1.length !== keys2.length)
            return false;

        var match = true;
        for(var i=0; i<keys1.length; i++) {
            if(atoms1[keys1[i]] !== atoms2[keys1[i]])
                match = false;
        }
        return match;
    }

    function check_shell(node) {
        var atom = node.getLabel();
        var bonds = node.getLinkedBonds();
        var implicitH = node.getImplicitHydrogenCount();
        var lonePairs = node.getLonePairCount();

        var bondNumber = implicitH;
        for(var j = 0; j < bonds.length; j++) {
            bondNumber += bonds[j].bondOrder;
        }

        if(atom === 'H') {
            if(bondNumber>1 || lonePairs > 0)
                return false;
        }
        if (['C','N','O','F'].includes(atom)) {
            if(bondNumber > 4 || bondNumber*2 + lonePairs*2 > 8)
                return false;
        }
        return true;
    }

    function check_formal_charge(node) {
        let atom = node.getLabel();
        let bonds = node.getLinkedBonds();
        let charge = node.charge;
        let implicitH = node.getImplicitHydrogenCount();
        let lonePairs = node.getLonePairCount();

        let bondNumber = implicitH;
        for(let j = 0; j < bonds.length; j++) {
            bondNumber += bonds[j].bondOrder;
        }

        let valence = {
            'H': 1,
            'C': 4,
            'N': 5,
            'O': 6,
            'F': 7,
        }

        let valence_electrons = valence[atom];

        if(valence_electrons === undefined)
            return undefined;

        let implicitPairs = 0;
        if (['N','O','F'].includes(atom))
            implicitPairs = (8 - bondNumber*2 - lonePairs*2) / 2;

        return valence_electrons - bondNumber - 2 * ( lonePairs + implicitPairs);
    }

    function check_electrons(node) {
        let atom = node.getLabel();
        let bonds = node.getLinkedBonds();
        let charge = node.charge;
        let implicitH = node.getImplicitHydrogenCount();
        let lonePairs = node.getLonePairCount();

        let bondNumber = implicitH;
        for(let j = 0; j < bonds.length; j++) {
            bondNumber += bonds[j].bondOrder;
        }

        let valence = {
            'H': 1,
            'B': 3,
            'C': 4,
            'N': 5,
            'O': 6,
            'F': 7,
            'Si': 4,
            'P': 5,
            'S': 6,
            'Cl': 7,
            'Br': 7,
            'I': 7,
        }

        let valenceElectrons = valence[atom] - charge;
        let electronCount = bondNumber*2 + lonePairs;

        let expectedPairs = undefined;
        let pairViolation = false;

        if (['N','O','F'].includes(atom)) { //This only accounts for closed shell atoms. Will not work for radicals/open shell.
            expectedPairs = (8 - bondNumber * 2) / 2;
            pairViolation = expectedPairs !== lonePairs;
        }
        else if(atom === 'H') {
            expectedPairs = 0;
            pairViolation = expectedPairs !== lonePairs;
        }

        return {valenceElectrons: valenceElectrons, electronCount: electronCount, lonePairs: lonePairs, pairViolation: pairViolation}

    }

    function checkTerminalHalogenViolation(node) {
        let atom = node.getLabel();
        let halogens = ['F', 'Cl', 'Br', 'I'];
        if(!halogens.includes(atom))
            return false;
        let bonds = node.getLinkedBonds();
        let implicitH = node.getImplicitHydrogenCount();
        let bondNumber = implicitH;
        for(let j = 0; j < bonds.length; j++) {
            bondNumber += bonds[j].bondOrder;
        }
        if(bondNumber > 1)
            return true;
        return false;
    }

    window.Echo.private('App.User.{{$user->id}}.Course.{{$course->id}}')
        .listen('WrittenAnswerGraded', (e) => {
            console.log(e);
            let questions = ractive2.get("questions");
            let ind = questions.findIndex(obj => obj.id === e.question_id);
            questions[ind].result = e.result;
            if(questions[ind].result.status === 2) //Retry
                $('#written_answer_'+e.question_id).summernote('enable');
            if(questions[ind].result.status === 0) //Recall was initiated
                $('#written_answer_'+e.question_id).summernote('disable');
            ractive2.update();
        })
    ;

    ractive2.on('changeWrittenSubmission', function(context) {
        let question = context.get();
        $('#written_answer_'+question.id).summernote('enable');
        question.result.status = 4;
        ractive2.update();
    });

    ractive2.on('submitWrittenAnswer', function(context) {
        var qid = context.node.id;
        var ind = context.node.name;

        $("#previewModal_"+ind).modal('hide');

        //Set message that response is being submitted.
        var questions = ractive2.get("questions");
        if(questions[ind].result == null)
            questions[ind].result = {};
        questions[ind].result.feedback = "Submitting...";
        ractive2.set("questions",questions);

        @if(isset($user))
        if(ractive2.get("student_view")&&ractive2.get("submit_for_student")) {
            submitWrittenAnswerPath = "{{url('instructor/course/'.$course->id.'/assignment/'.$assignment->id.'/submit_written_for_user/'.$user->id)}}";
            console.log("Submitting for student.");
        }
        else
            submitWrittenAnswerPath = "{{url('course/'.$course->id.'/assignment/'.$assignment->id.'/submitWrittenAnswer')}}";
        @endif

        $.post(submitWrittenAnswerPath,
            {
                _token: "{{ csrf_token() }}",
                question_id: qid,
                submission: questions[ind].answer.submission,
            })
            .done(function(response) {
                console.log(response);
                questions[ind].result.feedback = response.feedback;
                questions[ind].result.status = 0;
                questions[ind].result.valid = response.valid;
                $('#written_answer_'+qid).summernote('disable');
                ractive2.set("questions",questions);
            })
            .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                if(xhr.responseJSON.message == "CSRF token mismatch.")
                    questions[ind].result.feedback = "Error submitting response. Try reloading the page and check your inputs.";
                else
                    questions[ind].result.feedback = "Error submitting response. Try reloading the page and check your inputs.";
                ractive2.set("questions",questions);
                console.log(error);
                console.log(xhr);
            });
    });

    ractive2.on('previewWrittenAnswer', function(context) {
        var ind = context.node.name;
        var questions = ractive2.get("questions");

        //Set message that preview is being prepared.
        ractive2.set("submissionPreview","Preview being prepared....");
        ractive2.update();

        $.post(previewWrittenAnswerPath,
            {
                _token: "{{ csrf_token() }}",
                submission: questions[ind].answer.submission,
            })
            .done(function(response) {
                ractive2.set("submissionPreview",response);
                MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
            })
            .fail(function(xhr,status,error) {  //Deal with post failure.  Suggest page reload in case it's a session timeout.
                if(xhr.responseJSON.message == "CSRF token mismatch.")
                    ractive2.set("submissionPreview","Error checking with server. Try reloading the page.");
                else if(xhr.responseJSON.message != "")
                    ractive2.set("submissionPreview",xhr.responseJSON.message);
                else
                    ractive2.set("submissionPreview","Error checking with server. Try reloading the page.");
                console.log(error);
                console.log(xhr);
            });
    });

    ractive2.on("selectVariableChoice", function(context, q, v) {
        let variable = context.getParent().getParent().get();
        if(variable.answer === null)
            variable.answer = {};
        variable.answer.submission = context.get().value;
        ractive2.update();
        console.log(q,v,qdata.choiceName(q,v,variable.answer.submission));
        $('#dropdownMenuButton_'+q+'_'+v).html(qdata.choiceName(q,v,variable.answer.submission));  //Deal with Ractive MathJax conflict.
        MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
    });

    ractive2.on("formula_preview", function(context, q,v) {
        let variable = context.get();
        let submission = variable.answer.submission;
        preview_formula(q,v,submission);
    });

    ractive2.on("formula_charge", function(context,q,v,charge) {
        let variable = context.get();
        console.log(variable);
        if(variable.answer === null)
            variable.answer = {submission: {}};
        variable.answer.submission.charge = charge;
        ractive2.update();
        preview_formula(q,v,variable.answer.submission);
    });

    ractive2.on("formula_phase", function(context,q,v,phase) {
        let variable = context.get();
        console.log(variable);
        if(variable.answer === null)
            variable.answer = {submission: {}};
        variable.answer.submission.phase = phase;
        ractive2.update();
        preview_formula(q,v,variable.answer.submission);
    });

    function preview_formula(q,v,submission) {
        console.log(submission);
        let formula = submission.formula;
        let charge = submission.charge == null ? '' : submission.charge;
        let phase = submission.phase == null ? '' : submission.phase;
        preview = formula === '' ? '' : '$' + formula.replace(/(\d+)/g,'_{$1}') + '^{' + charge + '}' + '\\,' + phase + '$';
        let div = '#q_'+q+'_v_'+v+'_formula_preview';
        $(div).html(preview);
        MathJax.Hub.Queue(["Typeset",MathJax.Hub,div]);
    }

    ractive2.on("addReactionReactant", function(context) {
        let question = context.get();
        let answer = question.answer.submission;
        if(answer === "")
            answer = {};
        if(answer.reactants === undefined || answer.reactants == null)
            answer.reactants = [];
        answer.reactants.push({charge: null, phase: null});
        question.answer.submission = answer;
        ractive2.update();
    });

    ractive2.on("addReactionProduct", function(context) {
        let question = context.get();
        let answer = question.answer.submission;
        if(answer === "")
            answer = {};
        if(answer.products === undefined || answer.products == null)
            answer.products = [];
        answer.products.push({});
        question.answer.submission = answer;
        ractive2.update();
    });

    ractive2.on("reaction_formula_charge", function(context,q,v,charge) {
        let variable = context.get();
        console.log(variable);
        variable.charge = charge;
        ractive2.update();
        preview_reaction_assignment(q);
    });

    ractive2.on("reaction_formula_phase", function(context,q,v,phase) {
        let variable = context.get();
        console.log(variable);
        variable.phase = phase;
        ractive2.update();
        preview_reaction_assignment(q);
    });

    ractive2.on("removeSpecies", function(context, q) {
        let keypath = context.resolve();
        let arr = keypath.slice(0, keypath.lastIndexOf('.'));
        let ind = keypath.slice(keypath.lastIndexOf('.') + 1);
        this.splice(arr,ind,1);
        preview_reaction_assignment(q);
    });

    ractive2.on("reaction_preview", function(context, q) {
        preview_reaction_assignment(q);
    });

    function preview_reaction_assignment(q) {
        console.log("preview",q);
        let question = ractive2.get("questions."+q);
        console.log(question.answer);
        let preview = parse_reaction(question.answer.submission);
        console.log(preview);
        let div = '#q_'+q+'_reaction_preview_assignment';
        console.log(div, $(div).html());
        $(div).html(preview);
        MathJax.Hub.Queue(["Typeset",MathJax.Hub,div]);
    }

    function parse_reaction(reaction) {
        let str = "$\\ce{";
        if(reaction.reactants !== undefined)
            reaction.reactants.forEach((r, i) => {
                str += parse_formula(r);
                if(i<(reaction.reactants.length-1))
                    str += ' + ';
            });
        if(reaction.products !== undefined) {
            str += ' -> ';
            reaction.products.forEach((p, i) => {
                str += parse_formula(p);
                if(i<(reaction.products.length-1))
                    str += ' + ';
            });
        }
        str +="}$";
        return str;
    }

    function parse_formula(species) {
        let str = '';
        let formula = species.formula;
        let coefficient = species.coefficient == null ? '' : species.coefficient == 1 ? '' : species.coefficient;
        let charge = species.charge == null ? '' : species.charge;
        let phase = species.phase == null ? '' : species.phase;
        str = formula === '' ? '' : coefficient + formula + '^{' + charge + '} '  + phase;
        return str;
    }

    ractive2.on("selectMCChoice", function(context, id, qind, cind) {
        let choice = context.get();
        let question = context.getParent().getParent().get();
        let answer = question.answer.submission;
        id = id.toString();
        if(question.options.MC.type==="single") {
            if(answer.toString() === id)
                answer = "";
            else
                answer = id;
        }
        else if(question.options.MC.type==="multiple") {
            let answer_array;
            answer_array = answer === '' ? [] : answer.toString().split(',');
            let index = answer_array.indexOf(id);
            if(index > -1)
                answer_array.splice(index, 1);
            else
                answer_array.push(id);
            answer = answer_array.toString();
        }
        question.answer.submission = answer;
        ractive2.update();
    });

    function getQuestionVars(questions,ind) {
        let question_vars = {};
        if(questions[ind].type === 1) {
            let variables = questions[ind].variables;

            for (i = 0; i < variables.length; i++) {
                let processed = processVar(variables[i], ind);
                if(processed.status === 'error') {//End execution if there is an error.
                    return processed;
                    break;
                }
                question_vars[variables[i].name] = processed.value;
            }
        }
        else if([3,5].includes(questions[ind].type)) {
            let processed = processSimple(questions[ind], ind);
            if(processed.status === 'error') //End execution if there is an error.
                return processed;
            question_vars[questions[ind].id] = processed.value;
        }
        else if(questions[ind].type === 7) {
            let processed = processMC(questions[ind], ind);
            if (processed.status === 'error') //End execution if there is an error.
                return processed;
            question_vars[questions[ind].id] = processed.value;
        }
        else if(questions[ind].type === 8) {
            let processed = processReaction(questions[ind], ind);
            if (processed.status === 'error') //End execution if there is an error.
                return processed;
            question_vars[questions[ind].id] = processed.value;
        }
        return {status: 'success', question_vars: question_vars};
    }

    function processVar(variable,q_ind) {
        console.log(variable);
        if(variable.type !== 3 && (variable.answer === null || variable.answer.submission === ""))
            return {status: 'error', msg: 'Please enter an answer for ' +variable.title + '.'};
        if(variable.type === "0") {  //Numeric
            let val = sciNotation(variable.answer.submission);
            if (!$.isNumeric(val))
                return {status: 'error', msg : 'Please enter a numeric value for ' + variable.title + '. <br/><br/>Note that LabPal accepts scientific notation in the following forms (all without spaces): 1.2e-3, 1.2x10^-3, 1.2*10^-3'};
            return {status: 'success', value: val};
        }
        else if(variable.type === "1") {  //Array
            return processArr(variable,q_ind);
        }
        else if(variable.type === "2") {  //String
            return {status: 'success', value: variable.answer.submission};
        }
        /*else if(variable.type === "3") {  //Computed
            return {status: 'success', value: qdata.questions[0].computedVars[variable.name]};
        }*/
        else if(variable.type === "4") {  //Selection
            return {status: 'success', value: variable.answer.submission};
        }
        else if(variable.type === "5") {  //Chemical Formula
            return {status: 'success', value: JSON.stringify(variable.answer.submission)};
        }
    }

    function processSimple(question, q_ind) {
        let val;
        if(question.type === 3) {
            val = sciNotation(question.answer.submission);
            if (!$.isNumeric(val))
                return {status: 'error', msg: 'Please enter a numeric value. <br/><br/> Note that LabPal accepts scientific notation in the following forms (all without spaces): 1.2e-3, 1.2x10^-3, 1.2*10^-3'};
        }
        else if(question.type === 5 || question.type === 7)
            val = question.answer.submission;
        else
            return {status: 'error', msg: 'Problem with question definition.'};

        return {status: 'success', value: val};
    }

    function processMC(question, q_ind) {
        let answer_array;
        answer_array = question.answer.submission === '' ? [] : question.answer.submission.toString().split(',');
        let choice_ids = question.choices.map(x => parseInt(x.id));
        answer_array = answer_array.filter(x => {
            return choice_ids.includes(parseInt(x));
        });
        let val = answer_array.toString();
        if(val.length === 0)
            return {status: 'error', msg: 'No selection recorded. If you have selected an answer, try selecting again, refreshing the page, or use another updated browser.'};
        //return {status: 'success', value: val};
        return {status: 'success', value: question.answer.submission};
    }

    function processReaction(question, q_ind) {
        return {status: 'success', value: JSON.stringify(question.answer.submission)};
    }

    function processArr(variable,q_ind) {
        var arr = variable.answer.submission;
        var split = arr.trim().split("\n"); // .trim() removes lagging whitespace, returns,
        var result = [];
        for (var k = 0; k < split.length; k++) {
            let num = sciNotation(split[k]);
            if (!isNaN(num)) {
                num = parseFloat(num);
                result.push(num);
            }
            else
                return {status: 'error', msg : 'All arrays must contain only numeric values.  Check ' + variable.title + '.'};
        }
        return {status: 'success', value: result};

    }

    function sciNotation(value) {
        var pattern = /(\d|\D)\s*((x|\*)\s*10\^)(\d|-\d)/;	// regex looking for (v)(x or *) 10^ (+/-v);
        var pattern2 = /(10\^)(\d|-\d)/;  //regex to look for 10^(+/-v)
        if (pattern.test(value)) {
            value = value.replace(pattern, "$1e$4");
            console.log("Scientific notation detected; fixing notation: " + value);
        }
        else if (pattern2.test(value)) {
            value = value.replace(pattern2, "1e$2");
            console.log("Scientific notation detected; fixing notation: " + value);
        }
        return value;
    }

    ractive2.on("discuss", function(event) {
        var question = event.get();
        question.discuss === true ? question.discuss = false : question.discuss = true;
        ractive2.update();
        MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
        $('[data-toggle="tooltip"]').tooltip();
    });

    ractive2.on("extra", function(event) {
        var question = event.get();
        console.log(question);
        if(question.extra.view == null)
            question.extra.view = true;
        else
            question.extra.view === true ? question.extra.view = false : question.extra.view = true;
        ractive2.update();
        MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
    });

    ractive2.on("showNames", function(event) {
        var question = event.get();
        question.showNames === true ? question.showNames = false : question.showNames = true;
        ractive2.update();
        console.log(question);
    });

    ractive2.on("submitComment", function(event) {
        if(event.original.type==="keydown" && event.original.keyCode !== 13)
            return;  //Bail out if they were typing and didn't hit enter
        var question = event.get();
        if(question.newComment=== null || question.newComment==="")
            return;
        question.submitting = true;
        ractive2.update();
        $.post(submitCommentPath,
            {
                _token: "{{ csrf_token() }}",
                question_id: question.id,
                contents: question.newComment,
            })
            .done(function(response) {
                console.log(response);
                question.newComment = "";
                question.submitting = false;
                ractive2.update();
            })
            .fail(function(xhr,status,error) {  //Deal with post failure.
                console.log(error);
                console.log(xhr);
            });
    });

    course_socket
        .listen('NewComment', (e) => {
            if(e.assignment_id != ractive2.get("assignment").id) {
                return;
            }
            console.log(e);
            var questions = ractive2.get("questions");
            var index = questions.findIndex(x => x.id==e.question_id);
            questions[index].comments.push(
                {
                    question_id: e.question_id,
                    id: e.id,
                    contents: e.contents,
                }
            );
            ractive2.update();
            MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
        })
    ;

</script>
