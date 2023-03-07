using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace WordPressMigrationTool.Models
{
    public class MigrationServiceInput
    {
        public SiteInfo sourceSite { get; set; }
        public SiteInfo destinationSite { get; set; }
        public RichTextBox richTextBox { get; set; }
        public string[] previousMigrationStatus { get; set; }
        public MigrationUX migrationUxForm { get; set; }
        public bool retainWpFeatures { get; set; }

        public MigrationServiceInput(SiteInfo sourceSiteInfo, SiteInfo destinationSiteInfo, RichTextBox progressViewRTextBox, string[] previousMigrationStatus, MigrationUX migrationUxForm, bool retainWpFeatures)
        {
            this.sourceSite = sourceSiteInfo;
            this.destinationSite = destinationSiteInfo;
            this.richTextBox = progressViewRTextBox;
            this.previousMigrationStatus = previousMigrationStatus;
            this.migrationUxForm = migrationUxForm;
            this.retainWpFeatures = retainWpFeatures;
        }
    }
}
