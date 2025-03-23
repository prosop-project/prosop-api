<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum for activity log names
 */
enum ActivityLogName: string
{
    case ACTIVITYLOG_CLEAN_COMMAND_ACTIVITY = 'activitylog_clean_command_activity';
    case PERMISSION_MODEL_ACTIVITY = 'Permission_model_activity';
    case AWS_FACE_MODEL_ACTIVITY = 'AwsFace_model_activity';
    case AWS_USER_MODEL_ACTIVITY = 'AwsUser_model_activity';
    case ASSIGN_ROLE_TO_USER_ACTIVITY = 'assign_role_to_user_activity';
    case ROLE_MODEL_ACTIVITY = 'Role_model_activity';
    case GRANT_PERMISSION_TO_ROLE_ACTIVITY = 'grant_permission_to_role_activity';
    case LOGIN_USER_ACTIVITY = 'login_user_activity';
    case REMOVE_ROLE_FROM_USER_ACTIVITY = 'remove_role_from_user_activity';
    case ANALYSIS_OPERATION_MODEL_ACTIVITY = 'AnalysisOperation_model_activity';
    case AWS_SIMILARITY_RESULT_MODEL_ACTIVITY = 'AwsSimilarityResult_model_activity';
}
