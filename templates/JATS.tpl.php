<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE article PUBLIC "-//NLM//DTD JATS (Z39.96) Journal Publishing DTD v1.0 20120330//EN" "http://jats.nlm.nih.gov/publishing/1.0/JATS-journalpublishing1.dtd">
<article xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:mml="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" article-type="other" dtd-version="1.0">
	<front>
		<journal-meta>
			<?php
				$pub_id = str_replace( 'http://', '', get_bloginfo('url'));
				$pub_id = str_replace( 'https://', '', $pub_id);
				$pub_id = str_replace( '/', '', $pub_id);
			?>
			<journal-id journal-id-type="publisher-id"><?php echo $pub_id; ?></journal-id>
			<journal-title-group><journal-title><?php echo get_bloginfo('name'); ?></journal-title></journal-title-group>
			<publisher>
				<publisher-name><?php echo get_bloginfo('name'); ?></publisher-name>
			</publisher>
		</journal-meta>
		<article-meta>
			<?php
				if ( !empty($DOI) ){
					?>
					<article-id pub-id-type="doi"><?php echo $DOI; ?></article-id>
					<?php
				}
			?>
			<article-categories>
				<subj-group subj-group-type="heading">
					<?php
						foreach ($categories as $category){
							echo '<subject>'.$category.'</subject>';
						}
					?>
				</subj-group>
			</article-categories>
			<title-group>
				<article-title><?php echo $item_title; ?></article-title>
			</title-group>
			<contrib-group content-type="authors">
				<?php
				$c = 1;
				if ( !empty($authors) ){
					foreach ($authors as $author){
						$corresp = (true === $author['corresp'] ? 'corresp="yes"' : '');
				?>
						<contrib id="author-<?php echo $c++; ?>" contrib-type="author" <?php echo $corresp; ?>>
							<?php if ( !empty( $author['contrib_id'] ) ){
								echo '<contrib-id contrib-id-type="orcid">'.$author['contrib_id'].'</contrib-id>';
							} ?>
							<name>
								<surname><?php echo $author['lname']; ?></surname>
								<given-names><?php echo $author['fname']; ?></given-names>
							</name>
							<?php
								if ( !empty($author['email']) ){
									echo '<email>'.$author['email'].'</email>';
								}
							?>
						</contrib>
				<?php
					}
				}
				?>
			</contrib-group>
			<?php
				//<pub-date pub-type="epub" date-type="pub" iso-8601-date="2013-12-12">
			?>
			<pub-date pub-type="<?php echo $pub_type; ?>" date-type="pub" iso-8601-date="<?php echo $year.'-'.$month.'-'.$day; ?>">
			<day><?php echo $day; ?></day>
			<month><?php echo $month; ?></month>
			<year><?php echo $year; ?></year>
			</pub-date>
			<elocation-id><?php echo $DOI; ?></elocation-id>
		</article-meta>
	</front>
	<body>
		<sec id="abstract">
			<title>Abstract</title>

			<p><?php echo $excerpt; ?></p>

		</sec>
		<?php echo $the_content; ?>
	</body>
	<back></back>
</article>
