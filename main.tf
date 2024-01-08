terraform {
  backend "http" {
  }
}

module "codepipeline" {
  source = "git::https://gitlab.flowdevelopers.cl/devops/terraform-modules/codepipeline.git?ref=main"
  project_name = "$PIPELINE_NAME"
  source_bucket_file = "$CI_FILE_QA"
}